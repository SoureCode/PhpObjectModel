<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;
use SoureCode\PhpObjectModel\Model\DeclareModel;
use SoureCode\PhpObjectModel\Model\NamespaceModel;
use SoureCode\PhpObjectModel\Model\UseModel;
use SoureCode\PhpObjectModel\Node\NodeFinder;
use SoureCode\PhpObjectModel\Node\NodeManipulator;
use SoureCode\PhpObjectModel\Printer\PrettyPrinter;
use SoureCode\PhpObjectModel\Type\AbstractType;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\IntersectionType;
use SoureCode\PhpObjectModel\Type\UnionType;
use SoureCode\PhpObjectModel\ValueObject\AbstractNamespaceName;
use SoureCode\PhpObjectModel\ValueObject\ClassName;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

abstract class AbstractFile
{
    protected Lexer\Emulative $lexer;

    /**
     * @var Node[]
     */
    protected array $statements = [];

    /**
     * @var Node[]
     */
    protected array $oldStatements = [];

    protected array $oldTokens = [];

    protected Parser $parser;

    protected PrettyPrinterAbstract $printer;

    protected string $oldSourceCode = '';

    protected NodeManipulator $manipulator;

    protected NodeFinder $finder;

    public function __construct(string $sourceCode = '<?php')
    {
        $this->lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startTokenPos',
                'endTokenPos',
            ],
        ]);
        $this->parser = new Parser\Php7($this->lexer);
        $this->printer = new PrettyPrinter();

        $this->setSourceCode($sourceCode);

        $this->manipulator = new NodeManipulator();
        $this->finder = new NodeFinder();
    }

    public function getOldSourceCode(): string
    {
        return $this->oldSourceCode;
    }

    public function getStatements(): array
    {
        return $this->statements;
    }

    public function reparse(): void
    {
        $this->setSourceCode($this->getSourceCode());
    }

    public function getSourceCode(): string
    {
        return $this->printer->printFormatPreserving(
            $this->statements,
            $this->oldStatements,
            $this->oldTokens
        );
    }

    protected function setSourceCode(string $sourceCode): void
    {
        $this->oldSourceCode = $sourceCode;
        $this->oldStatements = $this->parser->parse($sourceCode) ?? [];
        $this->oldTokens = $this->lexer->getTokens();

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());
        $traverser->addVisitor(
            new NodeVisitor\NameResolver(null, [
                'replaceNodes' => false,
            ])
        );

        $this->statements = $traverser->traverse($this->oldStatements);
    }

    /**
     * @return UseModel[]
     */
    public function getUses(): array
    {
        /**
         * @var Node\Stmt\Use_[] $nodes
         */
        $nodes = $this->finder->find($this->statements, function (Node $node) {
            return $node instanceof Node\Stmt\Use_;
        });

        return array_map(function (Node\Stmt\Use_ $node) {
            $model = new UseModel($node);
            $model->setFile($this);

            return $model;
        }, $nodes);
    }

    public function addUse(UseModel|string|AbstractNamespaceName $model, string $alias = null): self
    {
        if (is_string($model) || $model instanceof AbstractNamespaceName) {
            $model = new UseModel($model, $alias);
        }

        $this->statements = $this->manipulator->addUse($this->statements, $model);

        return $this;
    }

    public function hasUse(string|AbstractNamespaceName|UseModel $namespace): bool
    {
        $namespace = $namespace instanceof UseModel ? $namespace->getNamespace() : $namespace;
        $namespace = is_string($namespace) ? new NamespaceName($namespace) : $namespace;

        $uses = $this->getUses();

        foreach ($uses as $use) {
            if ($use->getNamespace()->isSame($namespace)) {
                return true;
            }

            if ($use->hasAlias()) {
                $parts = $namespace->getParts();
                /**
                 * @var string $firstPart
                 */
                $firstPart = array_shift($parts);

                if ($use->getAlias() === $firstPart) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param class-string|ClassName $class
     */
    public function getUseNamespaceName(string|ClassName $class): ?AbstractNamespaceName
    {
        $class = is_string($class) ? new ClassName($class) : $class;

        $uses = $this->getUses();

        /**
         * @var array<string, UseModel> $aliasUses
         */
        $aliasUses = [];
        $aliasNamespaces = [];

        /**
         * @var array<string, UseModel> $nameUses
         */
        $directUses = [];

        foreach ($uses as $useModel) {
            if ($useModel->hasAlias()) {
                $aliasUses[$useModel->getAlias()] = $useModel;
                $aliasNamespaces[$useModel->getNamespace()->getName()] = $useModel;
            } else {
                $directUses[$useModel->getNamespace()->getName()] = $useModel;
            }
        }

        // a direct use statement exist
        if (array_key_exists($class->getName(), $directUses)) {
            return NamespaceName::fromString($class->getShortName());
        }

        // Alias for a single word class names or already used types
        if (array_key_exists($class->getName(), $aliasUses)) {
            $model = $aliasUses[$class->getName()];
            $alias = $model->getAlias();

            return NamespaceName::fromString($alias);
        }

        // Alias for a class
        if (array_key_exists($class->getName(), $aliasNamespaces)) {
            $model = $aliasNamespaces[$class->getName()];
            $alias = $model->getAlias();

            return NamespaceName::fromString($alias);
        }

        // alias for namespaces
        foreach ($aliasUses as $alias => $model) {
            $namespace = $model->getNamespace();
            $commonNamespace = $namespace->getLongestCommonNamespace($class);

            if ($commonNamespace && $commonNamespace->length() === $namespace->length()) {
                $relative = $commonNamespace->getNamespaceRelativeTo($class);

                return NamespaceName::fromString($alias . '\\' . $relative->getName());
            }
        }

        return null;
    }

    public function resolveUseName(string|ClassName $class): Node\Name
    {
        $class = is_string($class) ? new ClassName($class) : $class;
        $name = $this->getUseNamespaceName($class);

        if (null === $name) {
            $this->addUse($class);

            return $class->toNode();
        }

        return new Node\Name($name->getName(), [
            'resolvedName' => new Node\Name\FullyQualified($class->getName()),
        ]);
    }

    public function resolveClassType(ClassType $type): Node\Name
    {
        $className = $type->getClassName();

        return $this->resolveUseName($className);
    }

    public function resolveType(AbstractType $type): Node\ComplexType|Node\Name|null
    {
        if ($type instanceof ClassType) {
            $name = $this->resolveClassType($type);

            if ($type->isNullable()) {
                return new Node\NullableType($name);
            }

            return $name;
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            $types = $type->getTypes();
            /**
             * @var Node\UnionType|Node\IntersectionType $node
             */
            $node = $type->getNode();

            foreach ($types as $unionType) {
                if (!($unionType instanceof ClassType)) {
                    continue;
                }

                $typeNode = $this->resolveClassType($unionType);

                $node->types = array_map(function (Node $node) use ($typeNode, $unionType): Node {
                    if ($node instanceof Node\Name && $node->toString() === $unionType->getClassName()->getName()) {
                        return $typeNode;
                    }

                    return $node;
                }, $node->types);
            }

            return $node;
        }

        return null;
    }

    public function hasNamespace(): bool
    {
        $node = $this->finder->findFirstInstanceOf($this->statements, Node\Stmt\Namespace_::class);

        return null !== $node;
    }

    public function getNamespace(): NamespaceModel
    {
        /**
         * @var Node\Stmt\Namespace_|null $node
         */
        $node = $this->finder->findFirstInstanceOf($this->statements, Node\Stmt\Namespace_::class);

        if (null === $node) {
            throw new \RuntimeException('No namespace found');
        }

        $model = new NamespaceModel($node);
        $model->setFile($this);

        return $model;
    }

    public function setNamespace(string|NamespaceName|NamespaceModel $model): self
    {
        $model = is_string($model) ? new NamespaceName($model) : $model;
        $model = $model instanceof NamespaceName ? new NamespaceModel($model) : $model;

        if ($this->hasNamespace()) {
            $namespace = $this->getNamespace();

            $this->manipulator->replaceNode($this->statements, $namespace->getNode(), $model->getNode());

            return $this;
        }

        /**
         * @var Node\Stmt[] $statements
         */
        $statements = array_filter($this->statements, static function (Node $node): bool {
            return !$node instanceof Node\Stmt\Declare_;
        });

        foreach ($statements as $statement) {
            $this->statements = $this->manipulator->removeNode($this->statements, $statement);
        }

        $node = $model->getNode();
        $node->stmts = [...$statements];

        $this->statements = [...$this->statements, $node];

        return $this;
    }

    public function getDeclare(): DeclareModel
    {
        /**
         * @var Node\Stmt\Declare_|null $node
         */
        $node = $this->finder->findFirstInstanceOf($this->statements, Node\Stmt\Declare_::class);

        if (null === $node) {
            throw new \RuntimeException('No declare found');
        }

        $model = new DeclareModel($node);
        $model->setFile($this);

        return $model;
    }

    public function hasDeclare(): bool
    {
        $node = $this->finder->findFirstInstanceOf($this->statements, Node\Stmt\Declare_::class);

        return null !== $node;
    }

    public function setDeclare(?DeclareModel $model): self
    {
        if (null === $model) {
            if ($this->hasDeclare()) {
                $declare = $this->getDeclare();

                $this->statements = $this->manipulator->removeNode($this->statements, $declare->getNode());
            }

            return $this;
        }

        if ($this->hasDeclare()) {
            $declare = $this->getDeclare();

            $this->manipulator->replaceNode($this->statements, $declare->getNode(), $model->getNode());

            return $this;
        }

        $this->statements = [$model->getNode(), ...$this->statements];

        return $this;
    }

    // @todo remove use statement
}

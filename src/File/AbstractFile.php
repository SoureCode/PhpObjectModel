<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use LogicException;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;
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

    public function __construct(string $sourceCode)
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

    public function addUse(AbstractNamespaceName $namespace, string $alias = null): UseModel
    {
        if ($this->hasUse($namespace)) {
            throw new LogicException('Use statement already exists.');
        }

        if ($alias && $this->hasUse($alias)) {
            throw new LogicException('Use statement alias already exists.');
        }

        $node = new Node\Stmt\Use_([
            new Node\Stmt\UseUse($namespace->toFqcnNode(), $alias),
        ]);

        $model = new UseModel($node);

        $targetNode = $this->finder->findLastInstanceOf($this->statements, Node\Stmt\Use_::class);

        if ($targetNode) {
            $this->manipulator->insertAfter($this->statements, $targetNode, $node);

            return $model;
        }

        /**
         * @var Node\Stmt\Namespace_|null $namespaceNode
         */
        $namespaceNode = $this->finder->findLastInstanceOf($this->statements, Node\Stmt\Namespace_::class);

        if ($namespaceNode) {
            array_unshift($namespaceNode->stmts, $node);

            return $model;
        }

        array_unshift($this->statements, $node);

        return $model;
    }

    public function hasUse(string|AbstractNamespaceName $namespace): bool
    {
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

        foreach ($directUses as $namespaceName => $model) {
            $namespace = $model->getNamespace();
            $commonNamespace = $namespace->getLongestCommonNamespace($class);

            if ($commonNamespace && $commonNamespace->length() === $namespace->length()) {
                $relative = $commonNamespace->getNamespaceRelativeTo($class);

                return NamespaceName::fromString($namespaceName . '\\' . $relative->getName());
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

    // get namespace
    // set namespace
    // get use statements
    // remove use statement
}

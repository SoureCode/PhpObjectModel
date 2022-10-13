<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;
use SoureCode\PhpObjectModel\Node\NodeFinder;
use SoureCode\PhpObjectModel\Node\NodeManipulator;
use SoureCode\PhpObjectModel\Printer\PrettyPrinter;
use SoureCode\PhpObjectModel\Type\AbstractType;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\IntersectionType;
use SoureCode\PhpObjectModel\Type\UnionType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;
use SoureCode\PhpObjectModel\ValueObject\NamespacePathItem;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFile
{
    protected readonly string $path;

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

    public function __construct(string $path)
    {
        $this->path = $path;

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

        $this->setSourceCode($this->load());
        $this->manipulator = new NodeManipulator();
        $this->finder = new NodeFinder();
    }

    protected function load(): string
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->path)) {
            throw new \RuntimeException(sprintf('File "%s" does not exist.', $this->path));
        }

        return file_get_contents($this->path);
    }

    public function getOldSourceCode(): string
    {
        return $this->oldSourceCode;
    }

    public function getStatements(): array
    {
        return $this->statements;
    }

    public function save(): void
    {
        (new Filesystem())->dumpFile($this->path, $this->getSourceCode());
    }

    public function update(): void
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

    public function addUse(ClassName $class, string $alias = null): void
    {
        $node = new Node\Stmt\Use_([
            new Node\Stmt\UseUse($class->toNode(), $alias),
        ]);

        $targetNode = $this->finder->findLastInstanceOf($this->statements, Node\Stmt\Use_::class);

        if ($targetNode) {
            $this->manipulator->insertAfter($this->statements, $targetNode, $node);

            return;
        }

        /**
         * @var Node\Stmt\Namespace_|null $namespaceNode
         */
        $namespaceNode = $this->finder->findLastInstanceOf($this->statements, Node\Stmt\Namespace_::class);

        if ($namespaceNode) {
            array_unshift($namespaceNode->stmts, $node);

            return;
        }

        array_unshift($this->statements, $node);
    }

    public function hasUse(string $class): bool
    {
        /**
         * @var Node\Stmt\Use_[] $uses
         */
        $uses = $this->finder->findInstanceOf($this->statements, Node\Stmt\Use_::class);

        foreach ($uses as $useNode) {
            foreach ($useNode->uses as $useUseNode) {
                $name = NodeManipulator::resolveName($useUseNode->name);

                if ($name === $class) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param class-string|ClassName $class
     */
    public function getUseName(string|ClassName $class): ?string
    {
        $class = is_string($class) ? new ClassName($class) : $class;

        /**
         * @var Node\Stmt\UseUse[] $uses
         */
        $uses = $this->finder->findInstanceOf($this->statements, Node\Stmt\UseUse::class);

        /**
         * @var array<string, string> $aliasUses
         */
        $aliasUses = [];

        /**
         * @var array<array-key, string> $nameUses
         */
        $directUses = [];

        foreach ($uses as $node) {
            if ($node->alias) {
                $aliasUses[$node->alias->name] = NodeManipulator::resolveName($node->name);
            } else {
                $directUses[] = NodeManipulator::resolveName($node->name);
            }
        }

        if (in_array($class->getName(), $directUses, true)) {
            return $class->getShortName();
        }

        if (in_array($class->getName(), $aliasUses, true)) {
            $value = array_search($class->getName(), $aliasUses, true);

            return false !== $value ? $value : null;
        }

        foreach ($aliasUses as $alias => $namespace) {
            $namespaceItem = NamespacePathItem::fromString($namespace);
            $commonNamespace = NamespacePathItem::getCommonNamespace($namespaceItem, $class);

            if ($commonNamespace->length() > 1 && $commonNamespace->length() === $namespaceItem->length()) {
                return $alias . '\\' . $class->relativeTo($commonNamespace)->getName();
            }
        }

        foreach ($directUses as $namespace) {
            $namespaceItem = NamespacePathItem::fromString($namespace);
            $commonNamespace = NamespacePathItem::getCommonNamespace($namespaceItem, $class);

            if ($commonNamespace->length() > 1 && $commonNamespace->length() === $namespaceItem->length()) {
                return $class->relativeTo($commonNamespace)->getName();
            }
        }

        return null;
    }

    public function resolveUseName(string|ClassName $class): Node\Name
    {
        $class = is_string($class) ? new ClassName($class) : $class;
        $name = $this->getUseName($class);

        if (null === $name) {
            $this->addUse($class);

            return $class->toReferenceNode();
        }

        return new Node\Name($name, [
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

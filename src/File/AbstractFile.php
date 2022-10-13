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

    /**
     * @param class-string|string $class
     */
    public function addUse(string $class, string $alias = null): void
    {
        $node = new Node\Stmt\Use_([
            new Node\Stmt\UseUse(new Node\Name\FullyQualified($class), $alias),
        ]);

        $targetNode = $this->finder->findLastInstanceOf($this->statements, Node\Stmt\Use_::class);

        if ($targetNode) {
            $index = (int) array_search($targetNode, $this->statements, true);

            array_splice(
                $this->statements,
                $index + 1,
                0,
                [$node]
            );

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

            if ($commonNamespace->length() > 1) {
                return $alias . '\\' . $class->relativeTo($commonNamespace)->getName();
            }
        }

        foreach ($directUses as $namespace) {
            $namespaceItem = NamespacePathItem::fromString($namespace);
            $commonNamespace = NamespacePathItem::getCommonNamespace($namespaceItem, $class);

            if ($commonNamespace->length() > 1) {
                return $class->relativeTo($commonNamespace)->getName();
            }
        }

        return null;
    }

    // get namespace
    // set namespace
    // get use statements
    // add use statement
    // remove use statement
    // has use statement
}

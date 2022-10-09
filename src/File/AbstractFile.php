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

    // get namespace
    // set namespace
    // get use statements
    // add use statement
    // remove use statement
    // has use statement
}

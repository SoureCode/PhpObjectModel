<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use SoureCode\PhpObjectModel\Manipulator\FileManipulator;

abstract class AbstractFile
{
    protected readonly string $path;

    protected Lexer\Emulative $lexer;

    /**
     * @var Node[]
     */
    protected array $newStatements = [];

    /**
     * @var Node[]
     */
    protected array $oldStatements = [];

    protected array $oldTokens = [];

    protected Parser $parser;

    protected PrettyPrinterAbstract $printer;

    protected string $sourceCode;

    protected FileManipulator $manipulator;

    public function __construct(string $path)
    {
        $this->path = $path;

        $this->lexer = new Lexer\Emulative();
        $this->parser = new Parser\Php7($this->lexer);
        $this->printer = new Standard();

        $this->setSourceCode($this->load());
        $this->manipulator = new FileManipulator($this);
    }

    protected function load(): string
    {
        return file_get_contents($this->path);
    }

    public function save(): void
    {
        file_put_contents($this->path, $this->sourceCode);
    }

    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    public function getStatements(): array
    {
        return $this->newStatements;
    }

    protected function setSourceCode(string $sourceCode): void
    {
        $this->sourceCode = $sourceCode;
        $this->oldStatements = $this->parser->parse($sourceCode) ?? [];
        $this->oldTokens = $this->lexer->getTokens();

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());
        $traverser->addVisitor(
            new NodeVisitor\NameResolver(null, [
                'replaceNodes' => false,
            ])
        );

        $this->newStatements = $traverser->traverse($this->oldStatements);
    }
}

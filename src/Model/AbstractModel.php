<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\File\AbstractFile;
use SoureCode\PhpObjectModel\Node\NodeFinder;
use SoureCode\PhpObjectModel\Node\NodeManipulator;

/**
 * @template-covariant T of Node
 */
abstract class AbstractModel
{
    /**
     * @psalm-var T
     */
    protected Node $node;

    protected NodeFinder $finder;

    protected NodeManipulator $manipulator;

    protected ?AbstractFile $file = null;

    /**
     * @psalm-param T $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
        $this->finder = new NodeFinder();
        $this->manipulator = new NodeManipulator();
    }

    /**
     * @psalm-return T
     */
    public function getNode(): Node
    {
        return $this->node;
    }

    public function setFile(?AbstractFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): ?AbstractFile
    {
        return $this->file;
    }
}

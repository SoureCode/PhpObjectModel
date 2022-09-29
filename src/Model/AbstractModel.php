<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\File\AbstractFile;

/**
 * @template T of Node
 */
abstract class AbstractModel
{
    protected readonly AbstractFile $file;

    /**
     * @var T
     */
    protected readonly Node $node;

    /**
     * @param T $node
     */
    public function __construct(AbstractFile $file, Node $node)
    {
        $this->node = $node;
        $this->file = $file;
    }
}

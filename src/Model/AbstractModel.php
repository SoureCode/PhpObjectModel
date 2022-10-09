<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Node\NodeFinder;
use SoureCode\PhpObjectModel\Node\NodeManipulator;

/**
 * @template T of Node
 */
abstract class AbstractModel
{
    /**
     * @psalm-var T
     */
    protected Node $node;

    protected NodeFinder $finder;

    protected NodeManipulator $manipulator;

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
}

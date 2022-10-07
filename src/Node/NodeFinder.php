<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node;

use PhpParser\Node;
use PhpParser\NodeFinder as BaseNodeFinder;

class NodeFinder extends BaseNodeFinder
{
    /**
     * @param Node|Node[] $nodes
     */
    public function findLast(Node|array $nodes, callable $filter): ?Node
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $nodes = $this->find($nodes, $filter);
        $node = end($nodes);

        return false === $node ? null : $node;
    }

    /**
     * @param Node|Node[] $nodes
     *
     * @psalm-param class-string $class
     */
    public function findLastInstanceOf(Node|array $nodes, string $class): ?Node
    {
        return $this->findLast($nodes, function ($node) use ($class) {
            return $node instanceof $class;
        });
    }
}

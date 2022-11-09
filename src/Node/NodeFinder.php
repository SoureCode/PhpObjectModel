<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node;

use PhpParser\Node;
use PhpParser\NodeFinder as BaseNodeFinder;
use PhpParser\NodeTraverser;
use SoureCode\PhpObjectModel\Node\Visitor\FindClassTypesVisitor;

class NodeFinder extends BaseNodeFinder
{
    /**
     * @psalm-param Node|Node[] $nodes
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
     * @psalm-param Node|Node[]  $nodes
     * @psalm-param class-string $class
     */
    public function findLastInstanceOf(Node|array $nodes, string $class): ?Node
    {
        return $this->findLast($nodes, function (Node $node) use ($class) {
            return $node instanceof $class;
        });
    }

    /**
     * @psalm-param Node|Node[] $nodes
     *
     * @return Node\Name[]
     */
    public function findTypes(Node|array $nodes): array
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $visitor = new FindClassTypesVisitor();

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        return $visitor->getTypes();
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use SoureCode\PhpObjectModel\Node\Visitor\RemoveNodeVisitor;
use SoureCode\PhpObjectModel\Node\Visitor\ReplaceNodeVisitor;

class NodeManipulator
{
    /**
     * @psalm-param Node|Node[] $nodes
     */
    public function replaceNode(Node|array $nodes, Node $oldNode, Node $newNode): void
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ReplaceNodeVisitor($oldNode, $newNode));

        $traverser->traverse($nodes);
    }

    /**
     * @psalm-param Node|Node[] $nodes
     */
    public function removeNode(Node|array $nodes, Node $node): void
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RemoveNodeVisitor($node));

        $traverser->traverse($nodes);
    }
}

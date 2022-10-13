<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class InsertAfterVisitor extends NodeVisitorAbstract
{
    private Node $node;

    private Node $targetNode;

    public function __construct(Node $targetNode, Node $node)
    {
        $this->targetNode = $targetNode;
        $this->node = $node;
    }

    public function leaveNode(Node $node)
    {
        if ($node === $this->targetNode) {
            return [$node, $this->node];
        }
    }
}

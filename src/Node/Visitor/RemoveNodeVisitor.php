<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class RemoveNodeVisitor extends NodeVisitorAbstract
{
    private Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function leaveNode(Node $node)
    {
        if ($node === $this->node) {
            return NodeTraverser::REMOVE_NODE;
        }
    }
}

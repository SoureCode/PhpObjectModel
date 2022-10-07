<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ReplaceNodeVisitor extends NodeVisitorAbstract
{
    private Node $oldNode;
    private Node $newNode;

    public function __construct(Node $oldNode, Node $newNode)
    {
        $this->oldNode = $oldNode;
        $this->newNode = $newNode;
    }

    public function leaveNode(Node $node)
    {
        if ($node === $this->oldNode) {
            return $this->newNode;
        }
    }
}

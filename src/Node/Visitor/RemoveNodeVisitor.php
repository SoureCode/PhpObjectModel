<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class RemoveNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node[]
     */
    private array $nodes;

    /**
     * @param Node[]|Node $nodes
     */
    public function __construct(array|Node $nodes)
    {
        $this->nodes = is_array($nodes) ? $nodes : [$nodes];
    }

    public function leaveNode(Node $node)
    {
        if (in_array($node, $this->nodes, true)) {
            return NodeTraverser::REMOVE_NODE;
        }
    }
}

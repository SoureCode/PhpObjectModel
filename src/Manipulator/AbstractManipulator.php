<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Manipulator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

abstract class AbstractManipulator
{
    /**
     * @return Node[]
     */
    abstract protected function getAst(): array;

    public function findFirstNode(callable $filterCallback): ?Node
    {
        $traverser = new NodeTraverser();
        $visitor = new NodeVisitor\FirstFindingVisitor($filterCallback);
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->getAst());

        return $visitor->getFoundNode();
    }

    public function findLastNode(callable $filterCallback): ?Node
    {
        $traverser = new NodeTraverser();
        $visitor = new NodeVisitor\FindingVisitor($filterCallback);
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->getAst());

        $nodes = $visitor->getFoundNodes();
        $node = end($nodes);

        return false === $node ? null : $node;
    }


    /**
     * @return Node[]
     */
    public function findNodes(callable $filterCallback): array
    {
        $traverser = new NodeTraverser();
        $visitor = new NodeVisitor\FindingVisitor($filterCallback);
        $traverser->addVisitor($visitor);
        $traverser->traverse($this->getAst());

        return $visitor->getFoundNodes();
    }

}

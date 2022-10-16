<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use RuntimeException;
use SoureCode\PhpObjectModel\Model\UseModel;
use SoureCode\PhpObjectModel\Node\Visitor\AddUseVisitor;
use SoureCode\PhpObjectModel\Node\Visitor\InsertAfterVisitor;
use SoureCode\PhpObjectModel\Node\Visitor\RemoveNodeVisitor;
use SoureCode\PhpObjectModel\Node\Visitor\ReplaceNodeVisitor;

class NodeManipulator
{
    /**
     * @param Node|Node[] $nodes
     *
     * @return Node[]
     */
    public function replaceNode(Node|array $nodes, Node $oldNode, Node $newNode): array
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ReplaceNodeVisitor($oldNode, $newNode));

        return $traverser->traverse($nodes);
    }

    /**
     * @psalm-param Node|Node[] $nodes
     *
     * @return Node[]
     */
    public function removeNode(Node|array $nodes, Node $node): array
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RemoveNodeVisitor($node));

        return $traverser->traverse($nodes);
    }

    /**
     * @param Node|Node[] $nodes
     *
     * @return Node[]
     */
    public function insertAfter(Node|array $nodes, Node $targetNode, Node $node): array
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new InsertAfterVisitor($targetNode, $node));

        return $traverser->traverse($nodes);
    }

    /**
     * @return class-string
     */
    public static function resolveName(Node\Name $name): string
    {
        if ($name->hasAttribute('resolvedName')) {
            /**
             * @var Node\Name\FullyQualified|null $resolvedNameAttr
             */
            $resolvedNameAttr = $name->getAttribute('resolvedName');

            if ($resolvedNameAttr) {
                return $resolvedNameAttr->toString();
            }
        }

        return $name->toString();
    }

    /**
     * Resolves a argument to the class-string FQCN or string content.
     */
    public static function resolveArgument(Node\Arg $arg): string
    {
        if (($arg->value instanceof Node\Expr\ClassConstFetch) && ($arg->value->class instanceof Node\Name)) {
            return self::resolveName($arg->value->class);
        }

        if ($arg->value instanceof Node\Scalar\String_) {
            return $arg->value->value;
        }

        throw new RuntimeException('Could not resolve argument.');
    }

    /**
     * @param Node|Node[] $nodes
     *
     * @return Node[]
     */
    public function addUse(Node|array $nodes, UseModel $model): array
    {
        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new AddUseVisitor($model));

        return $traverser->traverse($nodes);
    }
}

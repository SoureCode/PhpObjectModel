<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use SoureCode\PhpObjectModel\Model\UseModel;
use SoureCode\PhpObjectModel\Node\NodeFinder;
use SoureCode\PhpObjectModel\Node\NodeManipulator;
use SoureCode\PhpObjectModel\ValueObject\AbstractNamespaceName;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

class AddUseVisitor extends NodeVisitorAbstract
{
    private ?string $alias;

    private AbstractNamespaceName $namespace;

    private UseModel $useModel;

    private bool $alreadyAdded = false;

    public function __construct(UseModel $useModel)
    {
        $this->useModel = $useModel;
        $this->namespace = $useModel->getNamespace();
        $this->alias = $useModel->hasAlias() ? $useModel->getAlias() : null;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Node\Stmt\UseUse) {
            if ($node->alias && $this->alias === $node->alias->name) {
                $this->alreadyAdded = true;

                return NodeTraverser::STOP_TRAVERSAL;
            }

            $namespace = NamespaceName::fromNode($node->name);

            if ($namespace->isSame($this->namespace)) {
                $this->alreadyAdded = true;

                return NodeTraverser::STOP_TRAVERSAL;
            }
        }

        return null;
    }

    public function afterTraverse(array $nodes): ?array
    {
        if ($this->alreadyAdded) {
            return null;
        }

        $node = $this->useModel->getNode();
        $finder = new NodeFinder();
        $manipulator = new NodeManipulator();

        $targetNode = $finder->findLastInstanceOf($nodes, Node\Stmt\Use_::class);

        if ($targetNode) {
            return $manipulator->insertAfter($nodes, $targetNode, $node);
        }

        /**
         * @var Node\Stmt\Namespace_|null $namespaceNode
         */
        $namespaceNode = $finder->findLastInstanceOf($nodes, Node\Stmt\Namespace_::class);

        if ($namespaceNode) {
            array_unshift($namespaceNode->stmts, $node);

            return $nodes;
        }

        $targetNode = $finder->findLastInstanceOf($nodes, Node\Stmt\Declare_::class);

        if ($targetNode) {
            return $manipulator->insertAfter($nodes, $targetNode, $node);
        }

        return [$node, ...$nodes];
    }
}

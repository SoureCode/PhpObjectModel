<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use RuntimeException;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

/**
 * @extends AbstractModel<Node\Stmt\Namespace_>
 */
class NamespaceModel extends AbstractModel
{
    public function __construct(Node\Stmt\Namespace_|string|NamespaceName $nodeOrNamespace)
    {
        if (is_string($nodeOrNamespace)) {
            $nodeOrNamespace = new NamespaceName($nodeOrNamespace);
        }

        if ($nodeOrNamespace instanceof NamespaceName) {
            $nodeOrNamespace = new Node\Stmt\Namespace_($nodeOrNamespace->toFqcnNode());
        }

        parent::__construct($nodeOrNamespace);
    }

    public function getName(): NamespaceName
    {
        if (null === $this->node->name) {
            throw new RuntimeException('Namespace has no name.');
        }

        return NamespaceName::fromNode($this->node->name);
    }

    public function setName(string|NamespaceName $name): void
    {
        $name = is_string($name) ? NamespaceName::fromString($name) : $name;

        $this->node->name = $name->toFqcnNode();
    }
}

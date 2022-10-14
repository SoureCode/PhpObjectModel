<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use LogicException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\ValueObject\AbstractNamespaceName;
use SoureCode\PhpObjectModel\ValueObject\ClassName;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

/**
 * @extends AbstractModel<Node\Stmt\Use_>
 */
class UseModel extends AbstractModel
{
    protected Node\Stmt\UseUse $useUse;

    public function __construct(Node\Stmt\Use_ $node)
    {
        parent::__construct($node);

        $this->useUse = $node->uses[0];
    }

    public function getNamespace(): AbstractNamespaceName
    {
        $name = $this->useUse->name;

        if ($this->hasAlias()) {
            return NamespaceName::fromNode($name);
        }

        return ClassName::fromNode($name);
    }

    public function setNamespace(AbstractNamespaceName $class): self
    {
        $this->useUse->name = new Node\Name($class->getName());

        return $this;
    }

    public function hasAlias(): bool
    {
        return null !== $this->useUse->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->useUse->alias = new Node\Identifier($alias);

        return $this;
    }

    public function getAlias(): string
    {
        if (!$this->hasAlias()) {
            throw new LogicException('Use statement has no alias.');
        }

        $alias = $this->useUse->alias?->name ?? null;

        if (null === $alias) {
            throw new LogicException('Use statement has no alias.');
        }

        return $alias;
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use RuntimeException;
use SoureCode\PhpObjectModel\Type\AbstractType;

/**
 * @extends AbstractModel<Node\Param>
 */
class ParameterModel extends AbstractModel
{
    public function __construct(Node\Param|string $node, AbstractType|string|null $type = null)
    {
        if (is_string($node)) {
            $node = new Node\Param(new Node\Expr\Variable($node));
        }

        parent::__construct($node);

        if (null !== $type) {
            $this->setType($type);
        }
    }

    public function hasType(): bool
    {
        return null !== $this->node->type;
    }

    public function getType(): ?AbstractType
    {
        if (null === $this->node->type) {
            return null;
        }

        return AbstractType::fromNode($this->node->type);
    }

    public function setType(AbstractType|null|string $type): self
    {
        if (null === $type) {
            $this->node->type = null;

            return $this;
        }

        if (is_string($type)) {
            $type = AbstractType::fromString($type);
        }

        $node = $type->getNode();

        if ($this->file) {
            $node = $this->file->resolveType($type);
        }

        $this->node->type = $node;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->node->var = new Node\Expr\Variable($name);

        return $this;
    }

    public function getName(): string
    {
        if ($this->node->var instanceof Node\Expr\Variable && is_string($this->node->var->name)) {
            return $this->node->var->name;
        }

        throw new RuntimeException('Could not get name of parameter.');
    }

    public function setDefault(?Node\Expr $default): self
    {
        // @todo create a value model for all the types

        if (null === $default) {
            $this->node->default = null;

            return $this;
        }

        $this->node->default = $default;

        return $this;
    }

    public function getDefault(): ?Node\Expr
    {
        return $this->node->default;
    }
}

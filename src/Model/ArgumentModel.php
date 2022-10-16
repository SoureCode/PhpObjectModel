<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use LogicException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;
use SoureCode\PhpObjectModel\Value\ClassConstValue;

/**
 * @extends AbstractModel<Node\Arg>
 */
class ArgumentModel extends AbstractModel
{
    public function __construct(Node\Arg|string $node, AbstractValue|Node\Expr $value = null)
    {
        if (is_string($node)) {
            $value = $value instanceof AbstractValue ? $value->getNode() : $value;

            if (null === $value) {
                throw new LogicException('Value can not be null if node is a string.');
            }

            $node = new Node\Arg(
                $value,
                false,
                false,
                [],
                new Node\Identifier($node)
            );
        }

        parent::__construct($node);
    }

    public function getName(): ?string
    {
        return $this->node->name?->name;
    }

    public function setName(?string $name): self
    {
        if (null === $name) {
            $this->node->name = null;
        } else {
            $this->node->name = new Node\Identifier($name);
        }

        return $this;
    }

    public function getValue(): AbstractValue|Node\Expr
    {
        $value = AbstractValue::fromNode($this->node->value);

        if (null !== $value) {
            return $value;
        }

        return $this->node->value;
    }

    public function setValue(AbstractValue|Node\Expr $value): self
    {
        if ($value instanceof AbstractValue) {
            $this->node->value = $value->getNode();

            return $this->importTypes();
        }

        $this->node->value = $value;

        return $this;
    }

    public function importTypes(): self
    {
        if ($this->file) {
            $value = $this->getValue();

            if ($value instanceof ClassConstValue) {
                $class = $value->getClass();
                $node = $value->getNode();

                $node->class = $this->file->resolveUseName($class);
            }
        }

        return $this;
    }
}

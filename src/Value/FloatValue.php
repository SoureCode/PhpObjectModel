<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

/**
 * @extends AbstractValue<Node\Scalar\DNumber>
 */
class FloatValue extends AbstractValue
{
    public function __construct(Node\Scalar\DNumber|float $node)
    {
        if (is_float($node)) {
            $node = new Node\Scalar\DNumber($node);
        }

        parent::__construct($node);
    }

    public function setValue(float $value): FloatValue
    {
        $this->node->value = $value;

        return $this;
    }

    public function getValue(): float
    {
        return $this->node->value;
    }
}

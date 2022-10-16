<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

/**
 * @extends AbstractValue<Node\Scalar\String_>
 */
class StringValue extends AbstractValue
{
    public function __construct(string|Node\Scalar\String_ $value)
    {
        if (is_string($value)) {
            $value = new Node\Scalar\String_($value);
        }

        parent::__construct($value);
    }

    public function setValue(string $value): StringValue
    {
        $this->node->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->node->value;
    }
}

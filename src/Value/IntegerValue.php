<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

/**
 * @extends AbstractValue<Node\Scalar\LNumber>
 */
class IntegerValue extends AbstractValue
{
    public function __construct(Node\Scalar\LNumber|int $node)
    {
        $node = is_int($node) ? new Node\Scalar\LNumber($node, [
            'kind' => Node\Scalar\LNumber::KIND_DEC,
        ]) : $node;

        parent::__construct($node);
    }

    public function getValue(): int
    {
        return $this->node->value;
    }

    public function setValue(int $value): void
    {
        $this->node->value = $value;
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

/**
 * @extends AbstractValue<Node\Expr\ConstFetch>
 */
class BooleanValue extends AbstractValue
{
    public function __construct(Node\Expr\ConstFetch|bool $node)
    {
        if (is_bool($node)) {
            $node = new Node\Expr\ConstFetch(
                new Node\Name($node ? 'true' : 'false')
            );
        }

        parent::__construct($node);
    }

    public function getValue(): bool
    {
        return 'true' === $this->node->name->getLast();
    }

    public function setValue(bool $value): void
    {
        $this->node->name = new Node\Name($value ? 'true' : 'false');
    }
}

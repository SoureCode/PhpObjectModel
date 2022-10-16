<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

/**
 * @extends AbstractValue<Node\Expr\ConstFetch>
 */
class NullValue extends AbstractValue
{
    public function __construct(Node\Expr\ConstFetch $node = null)
    {
        $node = $node ?? new Node\Expr\ConstFetch(
            new Node\Name('null')
        );

        parent::__construct($node);
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\Const;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Expr\ConstFetch>
 */
class PhpFloatMaxConstValue extends AbstractValue
{
    public function __construct(Node\Expr\ConstFetch $node = null)
    {
        if (null === $node) {
            $node = new Node\Expr\ConstFetch(new Node\Name('PHP_FLOAT_MAX'));
        }
        parent::__construct($node);
    }
}

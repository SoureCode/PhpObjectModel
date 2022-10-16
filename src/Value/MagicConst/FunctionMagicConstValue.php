<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\MagicConst;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Scalar\MagicConst\Function_>
 */
class FunctionMagicConstValue extends AbstractValue
{
    public function __construct(Node\Scalar\MagicConst\Function_ $node = null)
    {
        if (null === $node) {
            $node = new Node\Scalar\MagicConst\Function_();
        }
        parent::__construct($node);
    }
}

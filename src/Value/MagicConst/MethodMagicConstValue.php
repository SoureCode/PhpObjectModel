<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\MagicConst;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Scalar\MagicConst\Method>
 */
class MethodMagicConstValue extends AbstractValue
{
    public function __construct(Node\Scalar\MagicConst\Method $node = null)
    {
        if (null === $node) {
            $node = new Node\Scalar\MagicConst\Method();
        }
        parent::__construct($node);
    }
}

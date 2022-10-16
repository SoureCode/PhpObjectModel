<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\MagicConst;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Scalar\MagicConst\Line>
 */
class LineMagicConstValue extends AbstractValue
{
    public function __construct(Node\Scalar\MagicConst\Line $node = null)
    {
        if (null === $node) {
            $node = new Node\Scalar\MagicConst\Line();
        }
        parent::__construct($node);
    }
}

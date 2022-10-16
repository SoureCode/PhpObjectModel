<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\MagicConst;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Scalar\MagicConst\Trait_>
 */
class TraitMagicConstValue extends AbstractValue
{
    public function __construct(Node\Scalar\MagicConst\Trait_ $node = null)
    {
        if (null === $node) {
            $node = new Node\Scalar\MagicConst\Trait_();
        }
        parent::__construct($node);
    }
}

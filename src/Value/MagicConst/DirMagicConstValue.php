<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\MagicConst;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Scalar\MagicConst\Dir>
 */
class DirMagicConstValue extends AbstractValue
{
    public function __construct(Node\Scalar\MagicConst\Dir $node = null)
    {
        if (null === $node) {
            $node = new Node\Scalar\MagicConst\Dir();
        }
        parent::__construct($node);
    }
}

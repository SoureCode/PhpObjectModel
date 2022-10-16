<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\MagicConst;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Scalar\MagicConst\File>
 */
class FileMagicConstValue extends AbstractValue
{
    public function __construct(Node\Scalar\MagicConst\File $node = null)
    {
        if (null === $node) {
            $node = new Node\Scalar\MagicConst\File();
        }
        parent::__construct($node);
    }
}

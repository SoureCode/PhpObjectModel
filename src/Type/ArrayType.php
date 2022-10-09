<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;

class ArrayType extends CompoundType
{
    public function __construct()
    {
        parent::__construct(new Node\Identifier('array'));
    }
}

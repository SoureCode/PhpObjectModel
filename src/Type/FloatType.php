<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;

class FloatType extends ScalarType
{
    public function __construct()
    {
        parent::__construct(new Node\Identifier('float'));
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;

class ObjectType extends CompoundType
{
    public function __construct()
    {
        parent::__construct(new Node\Identifier('object'));
    }
}

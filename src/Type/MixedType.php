<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;
use RuntimeException;

class MixedType extends AbstractType
{
    public function __construct()
    {
        parent::__construct(new Node\Identifier('mixed'));
    }

    public function setNullable(bool $nullable): AbstractType
    {
        if ($nullable) {
            throw new RuntimeException('Mixed type can not be nullable.');
        }

        return $this;
    }

    public function isNullable(): bool
    {
        return true;
    }
}

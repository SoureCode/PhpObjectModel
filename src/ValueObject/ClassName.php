<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

class ClassName extends NamespacePathItem
{
    public static function fromString(string $name): self
    {
        return new self($name);
    }
}

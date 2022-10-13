<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

class NamespaceItem extends NamespacePathItem
{
    public function getNamespace(): NamespaceItem
    {
        return new self(implode('\\', array_slice($this->parts, 0, -1)));
    }

    public function class(string $name): ClassName
    {
        return new ClassName($this->getName() . '\\' . $name);
    }
}

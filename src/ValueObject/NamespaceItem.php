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

    public function isRoot(): bool
    {
        return 0 === $this->length();
    }

    public function parent(): ?self
    {
        if ($this->isRoot()) {
            return null;
        }

        return new self(implode('\\', array_slice($this->parts, 0, -1)));
    }

    public function namespace(string $name): self
    {
        return new self(implode('\\', array_merge($this->parts, [$name])));
    }

    public static function root(): self
    {
        return new self('');
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }
}

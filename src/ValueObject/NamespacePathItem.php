<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use Stringable;

class NamespacePathItem implements Stringable
{
    /**
     * @var string[]
     */
    protected array $parts;

    public function __construct(string $name)
    {
        $this->parts = explode('\\', $name);
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function getName(): string
    {
        return implode('\\', $this->parts);
    }

    public function getShortName(): string
    {
        return end($this->parts);
    }

    public function length(): int
    {
        return count($this->parts);
    }

    public static function getCommonNamespace(NamespacePathItem $classA, NamespacePathItem $classB): NamespacePathItem
    {
        $partsA = $classA->parts;
        $partsB = $classB->parts;

        $parts = [];

        while (count($partsA) > 0 && count($partsB) > 0 && $partsA[0] === $partsB[0]) {
            $parts[] = array_shift($partsA);
            array_shift($partsB);
        }

        return new self(implode('\\', $parts));
    }

    public function relativeTo(NamespacePathItem $namespace): self
    {
        $partsA = $this->parts;
        $partsB = $namespace->parts;

        while (count($partsA) > 0 && count($partsB) > 0 && $partsA[0] === $partsB[0]) {
            array_shift($partsA);
            array_shift($partsB);
        }

        return new self(implode('\\', $partsA));
    }

    public function isSame(NamespacePathItem $namespace): bool
    {
        return $this->getName() === $namespace->getName();
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use InvalidArgumentException;
use Stringable;

abstract class AbstractNamespace implements Stringable
{
    /**
     * @var string[]
     */
    protected array $parts;

    /**
     * @param string[]|string $nameOrParts
     */
    public function __construct(string|array $nameOrParts)
    {
        $this->parts = array_filter(is_string($nameOrParts) ? explode('\\', $nameOrParts) : $nameOrParts);

        if (0 === count($this->parts)) {
            throw new InvalidArgumentException('Name must not be empty.');
        }
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function getShortName(): string
    {
        $shortName = end($this->parts);

        if (empty($shortName)) {
            throw new InvalidArgumentException('Name must not be empty.');
        }

        return $shortName;
    }

    public function isSame(AbstractNamespace $namespace): bool
    {
        return $this->getName() === $namespace->getName();
    }

    public function getName(): string
    {
        return implode('\\', $this->parts);
    }

    public function length(): int
    {
        return count($this->parts);
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use PhpParser\Node;

class NamespaceName extends AbstractNamespaceName
{
    /**
     * @param string[]|string $nameOrParts
     */
    public static function fromString(array|string $nameOrParts): self
    {
        return new self($nameOrParts);
    }

    public static function fromNode(Node\Name $node): self
    {
        if ($node->hasAttribute('resolvedName')) {
            /**
             * @var Node\Name\FullyQualified|null $attribute
             */
            $attribute = $node->getAttribute('resolvedName');

            if ($attribute) {
                return new self($attribute->toString());
            }
        }

        return new self($node->toString());
    }

    public function class(string $name): ClassName
    {
        return new ClassName([...$this->parts, $name]);
    }

    public function getNamespace(): NamespaceName
    {
        return new self(array_slice($this->parts, 0, -1));
    }

    public function namespace(string $name): self
    {
        return new self([...$this->parts, $name]);
    }

    public function parent(): ?self
    {
        if (1 === $this->length()) {
            return null;
        }

        return new self(array_slice($this->parts, 0, -1));
    }
}

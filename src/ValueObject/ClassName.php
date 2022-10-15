<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use PhpParser\Node;

class ClassName extends AbstractNamespaceName
{
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

    /**
     * @param string[]|string $nameOrParts
     */
    public static function fromString(string|array $nameOrParts): self
    {
        return new self($nameOrParts);
    }

    public function getNamespace(): NamespaceName
    {
        $parts = $this->getParts();

        array_pop($parts);

        return new NamespaceName($parts);
    }

    public function toClassConstFetchNode(): Node\Expr\ClassConstFetch
    {
        return new Node\Expr\ClassConstFetch($this->toNode(), 'class');
    }
}

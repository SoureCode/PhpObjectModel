<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\ValueInterface;

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
        return new NamespaceName(array_slice($this->parts, 0, -1));
    }

    public function toClassConstFetchNode(): Node\Expr\ClassConstFetch
    {
        return new Node\Expr\ClassConstFetch($this->toNode(), 'class');
    }

    /**
     * @param ValueInterface[] $args
     */
    public function toNewNode(array $args = []): Node\Expr\New_
    {
        $args = array_map(static fn (ValueInterface $arg) => $arg->toArgument(), $args);

        return new Node\Expr\New_($this->toNode(), $args);
    }
}

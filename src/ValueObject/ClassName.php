<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\ValueObject;

use PhpParser\Node;

class ClassName extends NamespacePathItem
{
    public static function fromString(string $name): self
    {
        return new self($name);
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

    public function toNode(): Node\Name
    {
        return new Node\Name($this->getName());
    }

    public function toReferenceNode(): Node\Name
    {
        return new Node\Name($this->getShortName(), [
            'resolvedName' => new Node\Name\FullyQualified($this->getName()),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use LogicException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @extends AbstractModel<Node\AttributeGroup>
 */
class AttributeModel extends AbstractModel
{
    private Node\Attribute $attributeNode;

    public function __construct(Node\AttributeGroup|Node\Name|string|ClassName $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $nodeOrName = new ClassName($nodeOrName);
        }

        if ($nodeOrName instanceof ClassName) {
            $nodeOrName = new Node\Name($nodeOrName->getName());
        }

        if ($nodeOrName instanceof Node\Name) {
            $nodeOrName = new Node\AttributeGroup([
                new Node\Attribute($nodeOrName),
            ]);
        }

        $attributeNode = array_key_exists(0, $nodeOrName->attrs) ? $nodeOrName->attrs[0] : null;

        if (null === $attributeNode) {
            throw new LogicException('Attribute node is null.');
        }

        $this->attributeNode = $attributeNode;

        parent::__construct($nodeOrName);
    }

    public function getName(): ClassName
    {
        return ClassName::fromNode($this->attributeNode->name);
    }

    public function addArgument(Node\Arg $argument): self
    {
        $this->attributeNode->args = [...$this->attributeNode->args, $argument];

        return $this;
    }

    /**
     * @return Node\Arg[]
     */
    public function getArguments(): array
    {
        return $this->attributeNode->args;
    }

    /**
     * @param Node\Arg[] $args
     */
    public function setArguments(array $args): self
    {
        $this->attributeNode->args = [...$args];

        return $this;
    }
}

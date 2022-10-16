<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use LogicException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\Traits\Arguments;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @extends AbstractModel<Node\AttributeGroup>
 */
class AttributeModel extends AbstractModel
{
    use Arguments;

    private Node\Attribute $argumentsNode;

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

        $this->argumentsNode = $attributeNode;

        parent::__construct($nodeOrName);
    }

    public function getName(): ClassName
    {
        return ClassName::fromNode($this->argumentsNode->name);
    }

    public function importTypes(): self
    {
        if ($this->file) {
            $name = $this->getName();
            $name = $this->file->resolveUseName($name);

            $this->argumentsNode->name = $name;
        }

        return $this;
    }
}

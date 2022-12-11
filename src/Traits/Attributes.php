<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Traits;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\AttributeModel;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

trait Attributes
{
    /**
     * @return AttributeModel[]
     */
    public function getAttributes(): array
    {
        return array_map(function (Node\AttributeGroup $node) {
            $model = new AttributeModel($node);
            $model->setFile($this->file);

            return $model;
        }, $this->node->attrGroups);
    }

    public function hasAttribute(string|ClassName|AttributeModel $name): bool
    {
        if ($name instanceof AttributeModel) {
            $name = $name->getName();
        }

        $name = is_string($name) ? new ClassName($name) : $name;

        foreach ($this->node->attrGroups as $node) {
            foreach ($node->attrs as $attr) {
                $attrName = ClassName::fromNode($attr->name);

                if ($attrName->isSame($name)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAttribute(string|ClassName $name): AttributeModel
    {
        $name = is_string($name) ? new ClassName($name) : $name;

        foreach ($this->node->attrGroups as $node) {
            foreach ($node->attrs as $attr) {
                $attrName = ClassName::fromNode($attr->name);

                if ($attrName->isSame($name)) {
                    $model = new AttributeModel($node);
                    $model->setFile($this->file);

                    return $model;
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('Attribute "%s" not found', $name->getName()));
    }

    public function removeAttribute(AttributeModel|string|ClassName $model): self
    {
        if (is_string($model) || $model instanceof ClassName) {
            $model = $this->getAttribute($model);
        }

        $this->manipulator->removeNode($this->node, $model->getNode());

        return $this;
    }

    public function addAttribute(AttributeModel|string|ClassName $attribute): self
    {
        if (is_string($attribute) || $attribute instanceof ClassName) {
            $attribute = new AttributeModel($attribute);
        }

        if ($this->hasAttribute($attribute->getName())) {
            $name = $attribute->getName();
            throw new \InvalidArgumentException(sprintf('Attribute "%s" already exists', $name->getName()));
        }

        $this->node->attrGroups = [...$this->node->attrGroups, $attribute->getNode()];

        $attribute->setFile($this->file);
        $attribute->importTypes();

        return $this;
    }

    /**
     * @param AttributeModel[] $attributes
     */
    public function setAttributes(array $attributes): self
    {
        $this->node->attrGroups = [];

        foreach ($attributes as $attribute) {
            $this->addAttribute($attribute);
        }

        return $this;
    }
}

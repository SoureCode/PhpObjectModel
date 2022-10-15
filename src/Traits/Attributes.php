<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Traits;

use InvalidArgumentException;
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

    public function hasAttribute(string|ClassName $name): bool
    {
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

        throw new InvalidArgumentException(sprintf('Attribute "%s" not found', $name->getName()));
    }

    public function removeAttribute(AttributeModel|string|ClassName $model): self
    {
        if (is_string($model) || $model instanceof ClassName) {
            $model = $this->getAttribute($model);
        }

        $this->manipulator->removeNode($this->node, $model->getNode());

        return $this;
    }

    public function addAttribute(AttributeModel $model): self
    {
        if ($this->hasAttribute($model->getName())) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" already exists', $model->getName()->getName()));
        }

        if ($this->file) {
            $className = $model->getName();
            $name = $this->file->resolveUseName($className);

            $args = $model->getArguments();

            $model = new AttributeModel($name);
            $model->setArguments($args);
        }

        $model->setFile($this->file);

        $this->node->attrGroups = [...$this->node->attrGroups, $model->getNode()];

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use InvalidArgumentException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\Node\NodeManipulator;
use SoureCode\PhpObjectModel\Type\AbstractType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @extends AbstractClassLikeModel<Node\Stmt\Class_>
 */
class ClassModel extends AbstractClassLikeModel
{
    public function __construct(Node\Stmt\Class_|string $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $node = new Node\Stmt\Class_($nodeOrName);
        } else {
            $node = $nodeOrName;
        }

        parent::__construct($node);
    }

    /**
     * @return PropertyModel[]
     */
    public function getProperties(): array
    {
        /**
         * @var Node\Stmt\Property[] $nodes
         */
        $nodes = $this->finder->findInstanceOf($this->node, Node\Stmt\Property::class);

        return array_map(
            function (Node\Stmt\Property $node) {
                $model = new PropertyModel($node);
                $model->setFile($this->file);

                return $model;
            },
            $nodes
        );
    }

    public function hasProperty(string $name): bool
    {
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\Property && $node->props[0]->name->name === $name;
        });

        return null !== $node;
    }

    public function getProperty(string $name): PropertyModel
    {
        /**
         * @var Node\Stmt\Property|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\Property && $node->props[0]->name->name === $name;
        });

        if (null === $node) {
            throw new InvalidArgumentException(sprintf('Property "%s" not found.', $name));
        }

        $model = new PropertyModel($node);
        $model->setFile($this->file);

        return $model;
    }

    /**
     * If the property already exists, it will be overwritten.
     */
    public function addProperty(PropertyModel $property): self
    {
        if ($this->hasProperty($property->getName())) {
            $this->removeProperty($property->getName());
        }

        $node = $property->getNode();

        if (null !== $node->type) {
            $type = AbstractType::fromNode($node->type);

            if (null !== $this->file) {
                $resolveTypeNode = $this->file->resolveType($type);

                if (null !== $resolveTypeNode) {
                    $node->type = $resolveTypeNode;
                }
            }
        }

        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\Property::class);

        if (!$targetNode) {
            $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassConst::class);
        }

        if (!$targetNode) {
            $this->finder->findLastInstanceOf($this->node, Node\Stmt\TraitUse::class);
        }

        if ($targetNode) {
            $index = (int) array_search($targetNode, $this->node->stmts, true);

            array_splice(
                $this->node->stmts,
                $index + 1,
                0,
                [$node]
            );
        } else {
            array_unshift($this->node->stmts, $node);
        }

        return $this;
    }

    public function removeProperty(string $name): self
    {
        $property = $this->getProperty($name);

        $this->manipulator->removeNode($this->node, $property->getNode());

        return $this;
    }

    /**
     * @return ClassMethodModel[]
     */
    public function getMethods(): array
    {
        /**
         * @var Node\Stmt\ClassMethod[] $nodes
         */
        $nodes = $this->finder->findInstanceOf($this->node, Node\Stmt\ClassMethod::class);

        return array_map(function (Node\Stmt\ClassMethod $node) {
            $model = new ClassMethodModel($node);
            $model->setFile($this->file);

            return $model;
        }, $nodes);
    }

    public function hasMethod(string $name): bool
    {
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === $name;
        });

        return null !== $node;
    }

    public function getMethod(string $name): ClassMethodModel
    {
        /**
         * @var Node\Stmt\ClassMethod|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === $name;
        });

        if (null === $node) {
            throw new InvalidArgumentException(sprintf('Method "%s" not found.', $name));
        }

        $model = new ClassMethodModel($node);
        $model->setFile($this->file);

        return $model;
    }

    /**
     * If the method already exists, it will be overwritten.
     */
    public function addMethod(ClassMethodModel $model): self
    {
        if ($this->hasMethod($model->getName())) {
            $this->removeMethod($model->getName());
        }

        $node = $model->getNode();

        if (null !== $node->returnType) {
            $type = AbstractType::fromNode($node->returnType);

            $nodeType = $this->file?->resolveType($type);

            if (null !== $nodeType) {
                $node->returnType = $nodeType;
            }
        }

        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassMethod::class);

        if ($targetNode) {
            $index = (int) array_search($targetNode, $this->node->stmts, true);

            array_splice(
                $this->node->stmts,
                $index + 1,
                0,
                [$node]
            );
        } else {
            $this->node->stmts[] = $node;
        }

        return $this;
    }

    public function removeMethod(string $name): self
    {
        $method = $this->getMethod($name);
        $this->manipulator->removeNode($this->node, $method->getNode());

        return $this;
    }

    // @todo get constants

    // @todo has constant
    // @todo get constant
    // @todo add constant
    // @todo remove constant

    // @todo getTraits

    // @todo usesTrait (has)
    // @todo useTrait (add)
    // @todo removeTrait (remove)

    /**
     * @psalm-return class-string[]
     */
    public function getInterfaces(): array
    {
        return array_map(static function (Node\Name $node) {
            if ($node->hasAttribute('resolvedName')) {
                /**
                 * @var Node\Name\FullyQualified|null $attr
                 */
                $attr = $node->getAttribute('resolvedName');

                if ($attr) {
                    return $attr->toString();
                }
            }

            return $node->toString();
        }, $this->node->implements);
    }

    /**
     * @psalm-param  ClassName|class-string $name
     */
    public function implementInterface(ClassName|string $className): self
    {
        $className = is_string($className) ? new ClassName($className) : $className;

        if (!$this->implementsInterface($className)) {
            $node = $this->file?->resolveUseName($className) ?? $className->toNode();

            $this->node->implements[] = $node;
        }

        return $this;
    }

    /**
     * @psalm-param class-string $name
     */
    public function removeInterface(string $name): self
    {
        $this->node->implements = array_filter($this->node->implements, static function (Node\Name $node) use ($name) {
            return NodeManipulator::resolveName($node) !== $name;
        });

        $this->node->implements = array_values($this->node->implements);

        return $this;
    }

    /**
     * @param ClassName|class-string $className
     */
    public function implementsInterface(ClassName|string $className): bool
    {
        $className = is_string($className) ? new ClassName($className) : $className;

        foreach ($this->node->implements as $node) {
            $nodeClassName = ClassName::fromNode($node);

            if ($nodeClassName->isSame($className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @psalm-return class-string|null
     */
    public function getParent(): ?string
    {
        /**
         * @var Node\Name|null $node
         */
        $parent = $this->node->extends;

        if (!$parent) {
            return null;
        }

        // returns the FQCN
        if ($parent->hasAttribute('resolvedName')) {
            /**
             * @var Node\Name\FullyQualified|null $attr
             */
            $attr = $parent->getAttribute('resolvedName');

            if ($attr) {
                return $attr->toString();
            }
        }

        return $parent->toString();
    }

    /**
     * @psalm-param ClassName|class-string $className
     */
    public function extend(ClassName|string $className): self
    {
        $className = is_string($className) ? new ClassName($className) : $className;
        $node = $this->file?->resolveUseName($className) ?? $className->toNode();

        $this->node->extends = $node;

        return $this;
    }

    public function isAbstract(): bool
    {
        return $this->node->isAbstract();
    }

    public function setAbstract(bool $abstract): self
    {
        if ($abstract) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_ABSTRACT;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_ABSTRACT;
        }

        return $this;
    }

    public function isFinal(): bool
    {
        return $this->node->isFinal();
    }

    public function setFinal(bool $final): self
    {
        if ($final) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_FINAL;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_FINAL;
        }

        return $this;
    }

    public function isReadOnly(): bool
    {
        return $this->node->isReadOnly();
    }

    public function setReadOnly(bool $readOnly): self
    {
        if ($readOnly) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_READONLY;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_READONLY;
        }

        return $this;
    }

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

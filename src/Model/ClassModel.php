<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use InvalidArgumentException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Node\NodeManipulator;
use SoureCode\PhpObjectModel\Traits\Attributes;
use SoureCode\PhpObjectModel\Type\AbstractType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @extends AbstractClassLikeModel<Node\Stmt\Class_>
 */
class ClassModel extends AbstractClassLikeModel
{
    use Attributes;

    public function __construct(Node\Stmt\Class_|string|ClassName $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $nodeOrName = new ClassName($nodeOrName);
        }

        if ($nodeOrName instanceof ClassName) {
            $nodeOrName = new Node\Stmt\Class_($nodeOrName->getShortName());
        }

        parent::__construct($nodeOrName);
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

    public function hasProperty(string|PropertyModel $name): bool
    {
        if ($name instanceof PropertyModel) {
            $name = $name->getName();
        }

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

    public function addProperty(PropertyModel|string $property, AbstractType|string|null $type = null): self
    {
        if (is_string($property)) {
            $property = new PropertyModel($property, $type);
        }

        if ($this->hasProperty($property->getName())) {
            throw new InvalidArgumentException(sprintf('Property "%s" already exists.', $property->getName()));
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

    public function removeProperty(string|PropertyModel $name): self
    {
        $name = $name instanceof PropertyModel ? $name->getName() : $name;
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

    public function hasMethod(string|ClassMethodModel $name): bool
    {
        $name = $name instanceof ClassMethodModel ? $name->getName() : $name;
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

    public function addMethod(ClassMethodModel|string $model): self
    {
        if (is_string($model)) {
            $model = new ClassMethodModel($model);
        }

        if ($this->hasMethod($model->getName())) {
            throw new InvalidArgumentException(sprintf('Method "%s" already exists.', $model->getName()));
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

    public function removeMethod(string|ClassMethodModel $name): self
    {
        $name = $name instanceof ClassMethodModel ? $name->getName() : $name;
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
    public function removeInterface(string|ClassName $name): self
    {
        $name = $name instanceof ClassName ? $name->getName() : $name;

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

        return ClassName::fromNode($parent)->getName();
    }

    /**
     * @psalm-param ClassName|class-string|ClassFile $className
     */
    public function extend(ClassName|string|ClassFile $className): self
    {
        if ($className instanceof ClassFile) {
            $namespace = $className->getNamespace()->getName();
            $class = $className->getClass();

            $className = $namespace->class($class->getName());
        }

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
}

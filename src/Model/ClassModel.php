<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

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
        $propertyNodes = $this->finder->findInstanceOf($this->node, Node\Stmt\Property::class);

        return array_map(static fn (Node\Stmt\Property $propertyNode) => new PropertyModel($propertyNode), $propertyNodes);
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
            throw new \InvalidArgumentException(sprintf('Property "%s" not found.', $name));
        }

        return new PropertyModel($node);
    }

    /**
     * If the property already exists, it will be overwritten.
     */
    public function addProperty(PropertyModel $property): void
    {
        if ($this->hasProperty($property->getName())) {
            $this->removeProperty($property->getName());
        }

        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\Property::class);

        if (!$targetNode) {
            $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassConst::class);
        }

        if (!$targetNode) {
            $this->finder->findLastInstanceOf($this->node, Node\Stmt\TraitUse::class);
        }

        if ($targetNode) {
            $index = array_search($targetNode, $this->node->stmts);

            array_splice(
                $this->node->stmts,
                $index + 1,
                0,
                [$property->getNode()]
            );

            return;
        }

        array_unshift($this->node->stmts, $property->getNode());
    }

    public function removeProperty(string $name): void
    {
        $property = $this->getProperty($name);

        $this->manipulator->removeNode($this->node, $property->getNode());
    }

    /**
     * @return PropertyModel[]
     */
    public function getMethods(): array
    {
        $nodes = $this->finder->findInstanceOf($this->node, Node\Stmt\ClassMethod::class);

        return array_map(static function (Node\Stmt\ClassMethod $node) {
            return new PropertyModel($node);
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
            throw new \InvalidArgumentException(sprintf('Method "%s" not found.', $name));
        }

        return new ClassMethodModel($node);
    }

    public function addMethod(ClassMethodModel $model): void
    {
        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassMethod::class);

        if ($targetNode) {
            $index = array_search($targetNode, $this->node->stmts);

            array_splice(
                $this->node->stmts,
                $index + 1,
                0,
                [$model->getNode()]
            );

            return;
        }

        $this->node->stmts[] = $model->getNode();
    }

    public function removeMethod(string $name): void
    {
        $method = $this->getMethod($name);
        $this->manipulator->removeNode($this->node, $method->getNode());
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
        return $this->node->implements;
    }

    /**
     * @psalm-param  class-string $name
     */
    public function implementInterface(string $name): void
    {
        $this->node->implements[] = new Node\Name($name);
    }

    /**
     * @psalm-param  class-string $name
     */
    public function removeInterface(string $name): void
    {
        $this->node->implements = array_filter($this->node->implements, static function (Node\Name $node) use ($name) {
            return $node->toString() !== $name;
        });
    }

    /**
     * @psalm-param  class-string $name
     */
    public function implementsInterface(string $name): bool
    {
        foreach ($this->node->implements as $node) {
            if ($node->toString() === $name) {
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

        if ($parent->hasAttribute('resolvedName')) {
            return $parent->getAttribute('resolvedName')->toString();
        }

        return $parent->toString();
    }

    /**
     * @psalm-param class-string $name
     */
    public function extend(string $name): void
    {
        $this->node->extends = new Node\Name($name);
    }

    public function isAbstract(): bool
    {
        return $this->node->isAbstract();
    }

    public function setAbstract(bool $abstract): void
    {
        if ($abstract) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_ABSTRACT;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_ABSTRACT;
        }
    }

    public function isFinal(): bool
    {
        return $this->node->isFinal();
    }

    public function setFinal(bool $final): void
    {
        if ($final) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_FINAL;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_FINAL;
        }
    }

    public function isReadOnly(): bool
    {
        return $this->node->isReadOnly();
    }

    public function setReadOnly(bool $readOnly): void
    {
        if ($readOnly) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_READONLY;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_READONLY;
        }
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use InvalidArgumentException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\File\AbstractFile;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\File\InterfaceFile;
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

        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\Property::class);

        if (!$targetNode) {
            $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassConst::class);
        }

        if (!$targetNode) {
            $this->finder->findLastInstanceOf($this->node, Node\Stmt\TraitUse::class);
        }

        $node = $property->getNode();

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

        $property->setFile($this->file);
        $property->importTypes();

        return $this;
    }

    public function removeProperty(string|PropertyModel $name): self
    {
        $name = $name instanceof PropertyModel ? $name->getName() : $name;
        $property = $this->getProperty($name);

        $this->manipulator->removeNode($this->node, $property->getNode());

        return $this;
    }

    public function importTypes(): void
    {
        // re-set attributes
        foreach ($this->getAttributes() as $attribute) {
            $attribute->importTypes();
        }

        // re-set extend
        $extend = $this->getExtend();

        if (null !== $extend) {
            $this->extend($extend);
        }

        // re-set implements
        foreach ($this->getInterfaces() as $interface) {
            $this->implement($interface);
        }

        // re-set properties
        foreach ($this->getProperties() as $property) {
            $property->importTypes();
        }

        // re-set methods
        foreach ($this->getMethods() as $method) {
            $method->importTypes();
        }
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

    public function addMethod(ClassMethodModel|string $method): self
    {
        if (is_string($method)) {
            $method = new ClassMethodModel($method);
        }

        if ($this->hasMethod($method->getName())) {
            throw new InvalidArgumentException(sprintf('Method "%s" already exists.', $method->getName()));
        }

        $node = $method->getNode();

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

        $method->setFile($this->file);
        $method->importTypes();

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
     * @return ClassName[]
     */
    public function getInterfaces(): array
    {
        return array_map(static function (Node\Name $node) {
            return ClassName::fromNode($node);
        }, $this->node->implements);
    }

    /**
     * @psalm-param InterfaceModel|string|InterfaceFile|ClassName|class-string $name
     */
    public function implement(InterfaceModel|string|InterfaceFile|ClassName $className): self
    {
        $className = $this->resolveInterfaceClassName($className);

        if (!$this->implements($className)) {
            $node = $this->file?->resolveUseName($className) ?? $className->toNode();

            $this->node->implements[] = $node;
        }

        return $this;
    }

    /**
     * @psalm-param InterfaceModel|string|InterfaceFile|ClassName|class-string $className
     */
    public function removeInterface(InterfaceModel|string|InterfaceFile|ClassName $className): self
    {
        $className = $this->resolveInterfaceClassName($className)->getName();

        $this->node->implements = array_filter(
            $this->node->implements,
            static function (Node\Name $node) use ($className) {
                return NodeManipulator::resolveName($node) !== $className;
            }
        );

        $this->node->implements = array_values($this->node->implements);

        return $this;
    }

    /**
     * @param InterfaceModel|string|InterfaceFile|ClassName|class-string $className
     */
    public function implements(InterfaceModel|string|InterfaceFile|ClassName $className): bool
    {
        $className = $this->resolveInterfaceClassName($className);

        foreach ($this->node->implements as $node) {
            $nodeClassName = ClassName::fromNode($node);

            if ($nodeClassName->isSame($className)) {
                return true;
            }
        }

        return false;
    }

    public function getExtend(): ?string
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
     * @psalm-param ClassName|string|ClassFile|ClassModel|null $className
     */
    public function extend(ClassName|string|ClassFile|ClassModel|null $className): self
    {
        if (null === $className) {
            $this->node->extends = null;

            return $this;
        }

        $className = $this->resolveClassName($className);

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

    public function setFile(?AbstractFile $file): self
    {
        parent::setFile($file);

        $this->importTypes();

        return $this;
    }

    /**
     * @param PropertyModel[] $properties
     */
    public function setProperties(array $properties): self
    {
        foreach ($this->getProperties() as $property) {
            $this->removeProperty($property);
        }

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * @param ClassMethodModel[] $methods
     */
    public function setMethods(array $methods): self
    {
        foreach ($this->getMethods() as $method) {
            $this->removeMethod($method);
        }

        foreach ($methods as $method) {
            $this->addMethod($method);
        }

        return $this;
    }

    /**
     * @param ClassModel|ClassFile|string|ClassName|class-string $className
     */
    private function resolveClassName(ClassModel|ClassFile|string|ClassName $className): ClassName
    {
        $className = $className instanceof ClassFile ? $className->getClass() : $className;

        if ($className instanceof ClassModel) {
            $file = $className->getFile();

            if ($file) {
                $namespace = $file->getNamespace()->getName();
                $className = $namespace->class($className->getName());
            } else {
                $className = $className->getName();
            }
        }

        return is_string($className) ? new ClassName($className) : $className;
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\File\InterfaceFile;
use SoureCode\PhpObjectModel\Value\ValueInterface;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @template-covariant T of Node\Stmt\ClassLike
 *
 * @extends AbstractModel<T>
 */
abstract class AbstractClassLikeModel extends AbstractModel
{
    public function getName(): ClassName
    {
        /**
         * @var Node\Stmt\ClassLike $node
         */
        $node = $this->node;

        if (null === $node->name) {
            throw new \RuntimeException('Class name not found.');
        }

        if (null !== $this->file) {
            $namespace = $this->file->getNamespace();

            return $namespace->getName()->class($node->name->name);
        }

        return ClassName::fromString($node->name->name);
    }

    public function setName(string $name): self
    {
        $this->node->name = new Node\Identifier($name);

        return $this;
    }

    /**
     * @param InterfaceModel|string|class-string|InterfaceFile|ClassName $className
     */
    protected function resolveInterfaceClassName(InterfaceModel|string|InterfaceFile|ClassName $className): ClassName
    {
        $className = $className instanceof InterfaceFile ? $className->getInterface() : $className;

        if ($className instanceof InterfaceModel) {
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

    /**
     * @return ClassConstModel[]
     */
    public function getConstants(): array
    {
        /**
         * @var Node\Stmt\ClassConst[] $nodes
         */
        $nodes = $this->finder->findInstanceOf($this->node, Node\Stmt\ClassConst::class);

        return array_map(
            function (Node\Stmt\ClassConst $node) {
                $model = new ClassConstModel($node);
                $model->setFile($this->file);

                return $model;
            },
            $nodes
        );
    }

    public function hasConstant(string|ClassConstModel $name): bool
    {
        if ($name instanceof ClassConstModel) {
            $name = $name->getName();
        }

        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\ClassConst && $node->consts[0]->name->name === $name;
        });

        return null !== $node;
    }

    public function getConstant(string $name): ClassConstModel
    {
        /**
         * @var Node\Stmt\ClassConst|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\ClassConst && $node->consts[0]->name->name === $name;
        });

        if (null === $node) {
            throw new \RuntimeException(sprintf('Constant "%s" not found.', $name));
        }

        $model = new ClassConstModel($node);
        $model->setFile($this->file);

        return $model;
    }

    public function addConstant(ClassConstModel|string $model, ValueInterface $value = null): self
    {
        if (is_string($model)) {
            $model = new ClassConstModel($model, $value);
        }

        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassConst::class);

        $node = $model->getNode();

        if ($targetNode) {
            $this->manipulator->insertAfter($this->node, $targetNode, $node);
        } else {
            array_unshift($this->node->stmts, $node);
        }

        $model->setFile($this->file);
        // $model->importTypes(); // TODO?

        return $this;
    }

    public function removeConstant(string|ClassConstModel $name): self
    {
        $name = $name instanceof ClassConstModel ? $name->getName() : $name;
        $property = $this->getConstant($name);

        $this->manipulator->removeNode($this->node, $property->getNode());

        return $this;
    }
}

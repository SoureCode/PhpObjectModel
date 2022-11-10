<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use Exception;
use PhpParser\Node;
use SoureCode\PhpObjectModel\File\InterfaceFile;
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
            throw new Exception('Invalid class name.');
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
}

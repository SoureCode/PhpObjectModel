<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class ClassFile extends AbstractFile
{
    public function hasClass(): bool
    {
        return null !== $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Stmt\Class_;
        });
    }

    public function getClass(): ClassModel
    {
        /**
         * @var Node\Stmt\Class_|null $node
         */
        $node = $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Stmt\Class_;
        });

        if (null === $node) {
            throw new \RuntimeException('Class not found.');
        }

        $model = new ClassModel($node);
        $model->setFile($this);

        return $model;
    }

    public function setClass(ClassModel|string|ClassName $class): self
    {
        $class = is_string($class) ? new ClassName($class) : $class;

        if ($class instanceof ClassName) {
            $namespace = $class->getNamespace();
            $class = new ClassModel($class);
            $this->setNamespace($namespace);
        }

        if ($this->hasClass()) {
            $oldClass = $this->getClass();

            $this->manipulator->replaceNode($this->statements, $oldClass->getNode(), $class->getNode());
            $oldClass->setFile(null);
        } else {
            $this->statements = [...$this->statements, $class->getNode()];
        }

        $class->setFile($this);

        return $this;
    }
}

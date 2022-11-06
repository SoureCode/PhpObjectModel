<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use Exception;
use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\InterfaceModel;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class InterfaceFile extends AbstractFile
{
    public function hasInterface(): bool
    {
        return null !== $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Stmt\Interface_;
        });
    }

    public function getInterface(): InterfaceModel
    {
        /**
         * @var Node\Stmt\Interface_|null $node
         */
        $node = $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Stmt\Interface_;
        });

        if (null === $node) {
            throw new Exception('Interface not found.');
        }

        $model = new InterfaceModel($node);
        $model->setFile($this);

        return $model;
    }

    public function setInterface(InterfaceModel|string|ClassName $interface): self
    {
        $interface = is_string($interface) ? new ClassName($interface) : $interface;

        if ($interface instanceof ClassName) {
            $namespace = $interface->getNamespace();
            $interface = new InterfaceModel($interface);
            $this->setNamespace($namespace);
        }

        if ($this->hasInterface()) {
            $oldInterface = $this->getInterface();

            $this->manipulator->replaceNode($this->statements, $oldInterface->getNode(), $interface->getNode());
            $oldInterface->setFile(null);
        } else {
            $this->statements = [...$this->statements, $interface->getNode()];
        }

        $interface->setFile($this);

        return $this;
    }
}

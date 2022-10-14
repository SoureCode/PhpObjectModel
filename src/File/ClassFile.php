<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use Exception;
use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\ClassModel;

class ClassFile extends AbstractFile
{
    public function getClass(): ClassModel
    {
        /**
         * @var Node\Stmt\Class_|null $node
         */
        $node = $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Stmt\Class_;
        });

        if (null === $node) {
            throw new Exception('Class not found.');
        }

        $model = new ClassModel($node);
        $model->setFile($this);

        return $model;
    }

    public function setClass(ClassModel $model): void
    {
        $oldModel = $this->getClass();

        $this->manipulator->replaceNode($this->statements, $oldModel->getNode(), $model->getNode());
        $model->setFile($this);
        $oldModel->setFile(null);
    }
}

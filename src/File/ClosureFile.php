<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\ClosureModel;

class ClosureFile extends AbstractFile
{
    public function getClosure(): ClosureModel
    {
        /**
         * @var Node\Expr\Closure|null $node
         */
        $node = $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Expr\Closure;
        });

        $model = new ClosureModel($node);
        $model->setFile($this);

        return $model;
    }

    public function setClosure(ClosureModel $model): self
    {
        if ($this->hasClosure()) {
            $oldModel = $this->getClosure();

            $this->manipulator->replaceNode($this->statements, $oldModel->getNode(), $model->getNode());
            $model->setFile($this);
            $oldModel->setFile(null);

            return $this;
        }

        $this->statements = [...$this->statements, $model->getNode()];
        $model->setFile($this);

        return $this;
    }

    private function hasClosure()
    {
        return null !== $this->finder->findFirst($this->statements, function (Node $node) {
            return $node instanceof Node\Expr\Closure;
        });
    }
}

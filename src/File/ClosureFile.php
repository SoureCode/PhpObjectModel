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

        return new ClosureModel($node);
    }

    public function setClosure(ClosureModel $model): void
    {
        $oldModel = $this->getClosure();

        $this->manipulator->replaceNode($this->statements, $oldModel->getNode(), $model->getNode());
    }
}

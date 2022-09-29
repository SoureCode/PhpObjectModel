<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Manipulator;

use PhpParser\Node;

class ClassManipulator extends AbstractManipulator
{
    private Node\Stmt\Class_ $classNode;

    public function __construct(Node\Stmt\Class_ $classNode)
    {
        $this->classNode = $classNode;
    }

    protected function getAst(): array
    {
        return $this->classNode->stmts;
    }
}

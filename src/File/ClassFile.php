<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\File;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\ClassModel;

class ClassFile extends AbstractFile
{
    public function getClass(): ClassModel
    {
        /**
         * @var Node\Stmt\Class_|null $class
         */
        $node = $this->manipulator->findFirstNode(function (Node $node) {
            return $node instanceof Node\Stmt\Class_;
        });

        return new ClassModel($this, $node);
    }
    // get class
    // get namespace
    // set namespace
    // get use statements
    // add use statement
    // remove use statement
    // has use statement
}

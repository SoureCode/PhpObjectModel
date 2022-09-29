<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Manipulator;

use SoureCode\PhpObjectModel\File\AbstractFile;

class FileManipulator extends AbstractManipulator
{
    private AbstractFile $file;

    public function __construct(AbstractFile $file)
    {
        $this->file = $file;
    }

    protected function getAst(): array
    {
        return $this->file->getStatements();
    }
}

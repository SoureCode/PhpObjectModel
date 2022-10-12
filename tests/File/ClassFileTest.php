<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\File;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;

class ClassFileTest extends TestCase
{
    public function testGetSetClass(): void
    {
        $file = new ClassFile(__DIR__ . '/../Fixtures/ExampleClassA.php');
        $class = $file->getClass();
        $code = $file->getSourceCode();

        self::assertSame('ExampleClassA', $class->getName());
        self::assertStringContainsString('class ExampleClassA', $code);
        self::assertStringNotContainsString('class Bar', $code);

        $file->setClass(new ClassModel('Bar'));

        $code = $file->getSourceCode();

        self::assertStringContainsString('class Bar', $code);
        self::assertStringNotContainsString('class ExampleClassA', $code);
    }
}

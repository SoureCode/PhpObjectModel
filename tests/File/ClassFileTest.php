<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\File;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\ClosureModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;

use function PHPUnit\Framework\assertTrue;

class ClassFileTest extends TestCase
{
    public function testGetSetClass(): void
    {
        $file = new ClassFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleClassA.php'));
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

    /*
 *  use SoureCode\PhpObjectModel\Model as Test;
    use SoureCode\PhpObjectModel\Model\PropertyModel;
    use SoureCode\PhpObjectModel\Model\ClosureModel as CM;

    Test\ClassModel
    PropertyModel
    CM
 */

    public function testHasGetUse(): void
    {
        $file = new ClassFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleClassA.php'));

        assertTrue($file->hasUse(PropertyModel::class));
        assertTrue($file->hasUse(ClosureModel::class));
        assertTrue($file->hasUse('SoureCode\\PhpObjectModel\\Model'));

        self::assertSame('CM', $file->getUseNamespaceName(ClosureModel::class)->getName());
        self::assertSame('Test', $file->getUseNamespaceName('SoureCode\\PhpObjectModel\\Model')->getName());
        self::assertSame('PropertyModel', $file->getUseNamespaceName(PropertyModel::class)->getName());
        self::assertSame('Test\\RandomClass', $file->getUseNamespaceName('SoureCode\\PhpObjectModel\\Model\\RandomClass')->getName());
    }
}

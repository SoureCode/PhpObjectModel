<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassConstModel;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Value\IntegerValue;
use SoureCode\PhpObjectModel\Value\StringValue;

class ClassConstModelTest extends TestCase
{
    public function testGetSetName(): void
    {
        $classConst = new ClassConstModel('foo', new StringValue('bar'));

        self::assertSame('foo', $classConst->getName());

        $classConst->setName('bar');

        self::assertSame('bar', $classConst->getName());
    }

    public function testSetGetValue(): void
    {
        $classConst = new ClassConstModel('foo', new StringValue('bar'));

        self::assertInstanceOf(StringValue::class, $classConst->getValue());
        self::assertSame('bar', $classConst->getValue()->getValue());

        $classConst->setValue(new IntegerValue(100));

        self::assertInstanceOf(IntegerValue::class, $classConst->getValue());
        self::assertSame(100, $classConst->getValue()->getValue());
    }

    public function testGeneratedCode(): void
    {
        $file = new ClassFile('<?php');
        $class = new ClassModel('Foo');
        $file->setClass($class);

        $class->addConstant('foo', new StringValue('bar'));

        $code = $file->getSourceCode();

        self::assertStringContainsString("const foo = 'bar';\n", $code);
    }
}

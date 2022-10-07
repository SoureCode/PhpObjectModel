<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Tests\Fixtures\AbstractBaseClassA;
use SoureCode\PhpObjectModel\Tests\Fixtures\AbstractBaseClassB;

class ClassModelTest extends TestCase
{
    private ?ClassModel $class = null;

    private ?ClassFile $file = null;

    public function setUp(): void
    {
        $this->file = new ClassFile(__DIR__.'/../Fixtures/ExampleClassA.php');
        $this->class = $this->file->getClass();
    }

    public function tearDown(): void
    {
        $this->file = null;
        $this->class = null;
    }

    public function testGetSetName(): void
    {
        self::assertSame('ExampleClassA', $this->class->getName());

        $this->class->setName('Foo');

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('class Foo', $code);
        self::assertStringNotContainsString('class Bar', $code);
    }

    public function testGetSetReadOnly(): void
    {
        self::assertFalse($this->class->isReadOnly());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('readonly class ExampleClassA', $code);

        $this->class->setReadOnly(true);

        self::assertTrue($this->class->isReadOnly());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('readonly class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);
    }

    public function testGetSetFinal(): void
    {
        self::assertFalse($this->class->isFinal());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('final class ExampleClassA', $code);

        $this->class->setFinal(true);

        self::assertTrue($this->class->isFinal());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('final class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);
    }

    public function testGetSetAbstract(): void
    {
        self::assertFalse($this->class->isAbstract());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('abstract class ExampleClassA', $code);

        $this->class->setAbstract(true);

        self::assertTrue($this->class->isAbstract());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('abstract class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);
    }

    public function testParent(): void
    {
        self::assertSame(AbstractBaseClassA::class, $this->class->getParent());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('extends AbstractBaseClassA', $code);

        $this->class->extend(AbstractBaseClassB::class);

        self::assertSame(AbstractBaseClassB::class, $this->class->getParent());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('extends '.AbstractBaseClassB::class, $code);
    }
}

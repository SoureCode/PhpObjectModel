<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
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

    public function testGetSetProperty(): void
    {
        /*
            private static string $staticProperty = 'foo1';
            protected static string $staticProperty2 = 'foo2';
            public static string $staticProperty3 = 'foo3';

            private string $property = 'foo1';
            protected string $property2 = 'foo2';
            public string $property3 = 'foo3';

            private $property4 = 'foo4';
            protected $property5 = 'foo5';
            public $property6 = 'foo6';

            private ?string $property7 = null;
            protected ?string $property8 = "";
            public ?string $property9 = null;
         */
        $actual = $this->class->getProperty('staticProperty');

        self::assertSame('staticProperty', $actual->getName());
        self::assertTrue($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertFalse($actual->isReadonly());

        $this->class->addProperty(new PropertyModel("staticProperty"));

        $actual = $this->class->getProperty('staticProperty');

        self::assertSame('staticProperty', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertFalse($actual->isReadonly());
    }

    public function testGetProperties(): void
    {
        $actual = $this->class->getProperties();

        self::assertCount(12, $actual);
    }

    public function testGetMethod(): void
    {
        $actual = $this->class->getMethod('baz');

        self::assertSame('baz', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
    }
}

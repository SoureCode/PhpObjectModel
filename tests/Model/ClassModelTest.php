<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassMethodModel;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
use SoureCode\PhpObjectModel\Tests\Fixtures\AbstractBaseClassA;
use SoureCode\PhpObjectModel\Tests\Fixtures\AbstractBaseClassB;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleAInterface;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleBInterface;
use SoureCode\PhpObjectModel\Type\StringType;

class ClassModelTest extends TestCase
{
    private ?ClassModel $class = null;

    private ?ClassFile $file = null;

    public function setUp(): void
    {
        $this->file = new ClassFile(__DIR__ . '/../Fixtures/ExampleClassA.php');
        $this->class = $this->file->getClass();
    }

    public function tearDown(): void
    {
        $this->file = null;
        $this->class = null;
    }

    public function testGetSetName(): void
    {
        $code = $this->file->getSourceCode();

        self::assertSame('ExampleClassA', $this->class->getName());
        self::assertStringContainsString('class ExampleClassA', $code);

        $this->class->setName('Foo');

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('class Foo', $code);
        self::assertStringNotContainsString('class Bar', $code);
    }

    public function testGetSetReadOnly(): void
    {
        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isReadOnly());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('readonly class ExampleClassA', $code);

        $this->class->setReadOnly(true);

        self::assertTrue($this->class->isReadOnly());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('readonly class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);

        $this->class->setReadOnly(false);

        self::assertFalse($this->class->isReadOnly());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('readonly class ExampleClassA', $code);
    }

    public function testGetSetFinal(): void
    {
        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isFinal());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('final class ExampleClassA', $code);

        $this->class->setFinal(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($this->class->isFinal());
        self::assertStringContainsString('final class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);

        $this->class->setFinal(false);

        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isFinal());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('final class ExampleClassA', $code);
    }

    public function testGetSetAbstract(): void
    {
        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isAbstract());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('abstract class ExampleClassA', $code);

        $this->class->setAbstract(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($this->class->isAbstract());
        self::assertStringContainsString('abstract class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);

        $this->class->setAbstract(false);

        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isAbstract());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('abstract class ExampleClassA', $code);
    }

    public function testParent(): void
    {
        $code = $this->file->getSourceCode();

        self::assertSame(AbstractBaseClassA::class, $this->class->getParent());
        self::assertStringContainsString('extends AbstractBaseClassA', $code);

        $this->class->extend(AbstractBaseClassB::class);

        $code = $this->file->getSourceCode();

        self::assertSame(AbstractBaseClassB::class, $this->class->getParent());
        self::assertStringContainsString('extends ' . AbstractBaseClassB::class, $code);
    }

    public function testGetSetProperty(): void
    {
        $actual = $this->class->getProperty('staticProperty');
        $code = $this->file->getSourceCode();

        self::assertSame('staticProperty', $actual->getName());
        self::assertTrue($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertFalse($actual->isReadonly());
        self::assertStringContainsString("private static string \$staticProperty = 'foo1';", $code);

        $this->class->addProperty(new PropertyModel('staticProperty'));

        $actual = $this->class->getProperty('staticProperty');
        $code = $this->file->getSourceCode();

        self::assertSame('staticProperty', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertFalse($actual->isReadonly());
        self::assertStringContainsString('private $staticProperty;', $code);
    }

    public function testGetProperties(): void
    {
        $actual = $this->class->getProperties();

        self::assertCount(12, $actual);
    }

    public function testGetAddHasRemoveMethod(): void
    {
        $actual = $this->class->getMethod('baz');

        self::assertSame('baz', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertTrue($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertFalse($actual->isPrivate());
        self::assertFalse($actual->isAbstract());

        $actual = new ClassMethodModel('baz');
        $actual->setPrivate();
        $actual->addStatement(new Node\Stmt\Return_(new Node\Scalar\String_('foo')));
        $actual->setReturnType(new StringType());

        $this->class->addMethod($actual);

        $code = $this->file->getSourceCode();

        self::assertSame('baz', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertStringNotContainsString('public function baz(string $foo, int $bar): string', $code);
        self::assertStringContainsString('private function baz(): string', $code);
        self::assertStringContainsString('return \'foo\';', $code);
        self::assertTrue($this->class->hasMethod('baz'));

        $this->class->removeMethod('baz');

        self::assertFalse($this->class->hasMethod('baz'));
    }

    public function testImplementsRemoveImplementInterface(): void
    {
        $interfaces = $this->class->getInterfaces();
        $code = $this->file->getSourceCode();

        self::assertCount(1, $interfaces);
        self::assertEquals([ExampleAInterface::class], $interfaces);
        self::assertTrue($this->class->implementsInterface(ExampleAInterface::class));
        self::assertFalse($this->class->implementsInterface(ExampleBInterface::class));
        self::assertStringContainsString('implements ExampleAInterface' . PHP_EOL, $code);

        $this->class->implementInterface(ExampleBInterface::class);

        $interfaces = $this->class->getInterfaces();
        $code = $this->file->getSourceCode();

        self::assertCount(2, $interfaces);
        self::assertEquals([ExampleAInterface::class, ExampleBInterface::class], $interfaces);
        self::assertTrue($this->class->implementsInterface(ExampleAInterface::class));
        self::assertTrue($this->class->implementsInterface(ExampleBInterface::class));
        self::assertStringContainsString('implements ExampleAInterface, ' . ExampleBInterface::class . PHP_EOL, $code);

        $this->class->removeInterface(ExampleAInterface::class);

        $interfaces = $this->class->getInterfaces();
        $code = $this->file->getSourceCode();

        self::assertCount(1, $interfaces);
        self::assertEquals([ExampleBInterface::class], $interfaces);
        self::assertTrue($this->class->implementsInterface(ExampleBInterface::class));
        self::assertFalse($this->class->implementsInterface(ExampleAInterface::class));
        self::assertStringContainsString('implements ' . ExampleBInterface::class . PHP_EOL, $code);
    }
}

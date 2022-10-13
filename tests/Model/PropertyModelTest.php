<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class PropertyModelTest extends TestCase
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

    public function testSetGetName(): void
    {
        $property = $this->class->getProperty('property2');

        $code = $this->file->getSourceCode();

        self::assertSame('property2', $property->getName());
        self::assertStringContainsString("protected string \$property2 = 'foo2';", $code);

        $property->setName('property3');

        $code = $this->file->getSourceCode();

        self::assertSame('property3', $property->getName());
        self::assertStringContainsString("protected string \$property3 = 'foo2';", $code);
    }

    public function testSetGetStatic(): void
    {
        $property = $this->class->getProperty('property2');

        $code = $this->file->getSourceCode();

        self::assertFalse($property->isStatic());
        self::assertStringContainsString("protected string \$property2 = 'foo2';", $code);

        $property->setStatic(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($property->isStatic());
        self::assertStringContainsString("protected static string \$property2 = 'foo2';", $code);
    }

    public function testGetSetAbstract(): void
    {
        $property = $this->class->getProperty('property2');

        $code = $this->file->getSourceCode();

        self::assertFalse($property->isAbstract());
        self::assertStringContainsString("protected string \$property2 = 'foo2';", $code);

        $property->setAbstract(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($property->isAbstract());
        self::assertStringContainsString("protected abstract string \$property2 = 'foo2';", $code);
    }

    public function testGetSetPrivate(): void
    {
        $property = $this->class->getProperty('property2');

        $code = $this->file->getSourceCode();

        self::assertFalse($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());
        self::assertStringContainsString("protected string \$property2 = 'foo2';", $code);

        $property->setPrivate();

        $code = $this->file->getSourceCode();

        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertStringContainsString("private string \$property2 = 'foo2';", $code);
    }

    public function testGetSetProtected(): void
    {
        $property = $this->class->getProperty('property');

        $code = $this->file->getSourceCode();

        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertStringContainsString("private string \$property = 'foo1';", $code);

        $property->setProtected();

        $code = $this->file->getSourceCode();

        self::assertFalse($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());
        self::assertStringContainsString("protected string \$property = 'foo1';", $code);
    }

    public function testGetSetPublic(): void
    {
        $property = $this->class->getProperty('property');

        $code = $this->file->getSourceCode();

        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertStringContainsString("private string \$property = 'foo1';", $code);

        $property->setPublic();

        $code = $this->file->getSourceCode();

        self::assertFalse($property->isPrivate());
        self::assertTrue($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertStringContainsString("public string \$property = 'foo1';", $code);
    }

    public function testGetSetReadonly(): void
    {
        $property = $this->class->getProperty('property');

        $code = $this->file->getSourceCode();

        self::assertFalse($property->isReadonly());
        self::assertStringContainsString("private string \$property = 'foo1';", $code);

        $property->setReadonly(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($property->isReadonly());
        self::assertStringContainsString("private readonly string \$property = 'foo1';", $code);
    }

    public function testGetSetType(): void
    {
        $property = $this->class->getProperty('property');

        $code = $this->file->getSourceCode();

        self::assertInstanceOf(StringType::class, $property->getType());
        self::assertStringContainsString("private string \$property = 'foo1';", $code);

        $property->setType(new ClassType(ClassName::class));

        $code = $this->file->getSourceCode();

        self::assertInstanceOf(ClassType::class, $property->getType());
        self::assertStringContainsString("private ClassName \$property = 'foo1';", $code);
        self::assertStringContainsString(sprintf('use %s;', ClassName::class), $code);
    }
}

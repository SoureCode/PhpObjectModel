<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;

class PropertyModelTest extends TestCase
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

    public function testSetGetName(): void
    {
        $property = $this->class->getProperty('property2');

        self::assertSame('property2', $property->getName());

        $property->setName('property3');

        self::assertSame('property3', $property->getName());
    }

    public function testSetGetStatic(): void
    {
        $property = $this->class->getProperty('property2');

        self::assertFalse($property->isStatic());

        $property->setStatic(true);

        self::assertTrue($property->isStatic());
    }

    public function testGetSetAbstract(): void
    {
        $property = $this->class->getProperty('property2');

        self::assertFalse($property->isAbstract());

        $property->setAbstract(true);

        self::assertTrue($property->isAbstract());
    }

    public function testGetSetPrivate(): void
    {
        $property = $this->class->getProperty('property2');

        self::assertFalse($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());

        $property->setPrivate(true);

        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
    }

    public function testGetSetProtected(): void
    {
        $property = $this->class->getProperty('property');

        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());

        $property->setProtected(true);

        self::assertFalse($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());
    }

    public function testGetSetPublic(): void
    {
        $property = $this->class->getProperty('property');

        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());

        $property->setPublic(true);

        self::assertFalse($property->isPrivate());
        self::assertTrue($property->isPublic());
        self::assertFalse($property->isProtected());
    }

    public function testGetSetReadonly(): void
    {
        $property = $this->class->getProperty('property');

        self::assertFalse($property->isReadonly());

        $property->setReadonly(true);

        self::assertTrue($property->isReadonly());
    }

}

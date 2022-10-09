<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Type;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleAInterface;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleBInterface;
use SoureCode\PhpObjectModel\Type\AbstractType;
use SoureCode\PhpObjectModel\Type\ArrayType;
use SoureCode\PhpObjectModel\Type\BooleanType;
use SoureCode\PhpObjectModel\Type\CallableType;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\FloatType;
use SoureCode\PhpObjectModel\Type\IntegerType;
use SoureCode\PhpObjectModel\Type\IntersectionType;
use SoureCode\PhpObjectModel\Type\IterableType;
use SoureCode\PhpObjectModel\Type\MixedType;
use SoureCode\PhpObjectModel\Type\NullType;
use SoureCode\PhpObjectModel\Type\ObjectType;
use SoureCode\PhpObjectModel\Type\ResourceType;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\Type\UnionType;
use SoureCode\PhpObjectModel\Type\VoidType;

class TypeTest extends TestCase
{
    private ?ClassModel $class = null;

    private ?ClassFile $file = null;

    public function provideTypes()
    {
        return [
            [new StringType(), 'string'],
            [(new StringType())->setNullable(true), '?string'],
            [new IntegerType(), 'int'],
            [(new IntegerType())->setNullable(true), '?int'],
            [new ArrayType(), 'array'],
            [(new ArrayType())->setNullable(true), '?array'],
            [new BooleanType(), 'bool'],
            [(new BooleanType())->setNullable(true), '?bool'],
            [new CallableType(), 'callable'],
            [(new CallableType())->setNullable(true), '?callable'],
            [new ClassType(ExampleBInterface::class), ExampleBInterface::class],
            [(new ClassType(ExampleBInterface::class))->setNullable(true), '?'.ExampleBInterface::class],
            [new FloatType(), 'float'],
            [(new FloatType())->setNullable(true), '?float'],
            [new IterableType(), 'iterable'],
            [(new IterableType())->setNullable(true), '?iterable'],
            [new MixedType(), 'mixed'],
            [new NullType(), 'null'],
            [new ObjectType(), 'object'],
            [(new ObjectType())->setNullable(true), '?object'],
            [new VoidType(), 'void'],
            [new ResourceType(), 'resource'],
            [(new ResourceType())->setNullable(true), '?resource'],
            [new IntersectionType([new ClassType(ExampleBInterface::class), new ClassType(ExampleAInterface::class)]), ExampleBInterface::class.'&'.ExampleAInterface::class],
            [new UnionType([new StringType(), new IntegerType()]), 'string|int'],
            [(new UnionType([new StringType(), new IntegerType()]))->setNullable(true), 'string|int|null'],
        ];
    }

    public function setUp(): void
    {
        $this->file = new ClassFile(__DIR__.'/../Fixtures/ExampleClassTypes.php');
        $this->class = $this->file->getClass();
    }

    public function tearDown(): void
    {
        $this->file = null;
        $this->class = null;
    }

    /**
     * @dataProvider provideTypes
     */
    public function testAllTypes(?AbstractType $type, string $expected): void
    {
        $prop = new PropertyModel('yeet');
        $prop->setType($type);

        $this->class->addProperty($prop);

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('private '.$expected.' $yeet;', $code);
    }
}

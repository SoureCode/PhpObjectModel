<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClosureFile;
use SoureCode\PhpObjectModel\Model\ClosureModel;
use SoureCode\PhpObjectModel\Model\ParameterModel;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class ParameterModelTest extends TestCase
{
    public function testSetTypeResolvesUseStatementWhenClassName(): void
    {
        $file = new ClosureFile('<?php');
        $closure = new ClosureModel();
        $file->setClosure($closure);

        $closure->addParam(
            (new ParameterModel('foo'))
                ->setType(new ClassType(ClassName::class))
        );

        $code = $file->getSourceCode();

        self::assertStringContainsString('(ClassName $foo)', $code);
        self::assertStringContainsString('use ' . ClassName::class . ';', $code);
    }

    public function testGetSetName(): void
    {
        $parameter = new ParameterModel('foo');

        self::assertSame('foo', $parameter->getName());

        $parameter->setName('bar');

        self::assertSame('bar', $parameter->getName());
    }

    public function testGetSetHasType(): void
    {
        $parameter = new ParameterModel('foo');

        self::assertFalse($parameter->hasType());
        self::assertNull($parameter->getType());

        $parameter->setType(new ClassType(ClassName::class));

        self::assertTrue($parameter->hasType());
        self::assertSame(ClassName::class, $parameter->getType()->getClassName()->getName());
    }

    public function testSetGetDefault(): void
    {
        $parameter = new ParameterModel('foo');

        self::assertNull($parameter->getDefault());

        $parameter->setDefault(new Node\Scalar\String_('bar'));

        self::assertSame('bar', $parameter->getDefault()->value);
    }

    public function testGeneratedCode(): void
    {
        $file = new ClosureFile('<?php');
        $closure = new ClosureModel();
        $file->setClosure($closure);

        $closure->addParam(
            (new ParameterModel('foo'))
                ->setType(new StringType())
                ->setDefault(new Node\Scalar\String_('bar'))
                ->setName('bar')
        );

        $code = $file->getSourceCode();

        self::assertStringContainsString('(string $bar = \'bar\')', $code);
    }
}

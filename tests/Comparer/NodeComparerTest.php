<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Comparer;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\Comparer\AbstractNodeComparer;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassMethodModel;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\ParameterModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
use SoureCode\PhpObjectModel\Value\ClassConstValue;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class NodeComparerTest extends TestCase
{
    public function testShouldMatch(): void
    {
        $classFile = new ClassFile();
        $class = new ClassModel('Foo');

        $classFile->setClass($class);

        $property = new PropertyModel('bar');
        $class->addProperty($property);

        $constructor = new ClassMethodModel('__construct');
        $class->addMethod($constructor);
        $parameter = new ParameterModel('foo');

        $constructor->addStatement(
            $property->assignTo($parameter)
        );

        $code = $classFile->getSourceCode();

        self::assertStringContainsString('$this->bar = $foo;', $code);

        $rhs = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    'bar'
                ),
                new Node\Expr\Variable('foo')
            )
        );

        $statements = $constructor->getStatements();
        $lhs = $statements[0];

        $result = AbstractNodeComparer::compareNodes($lhs, $rhs);

        $this->assertTrue($result);
    }

    public function testShouldAlsoMatch(): void
    {
        $classFile = new ClassFile();
        $class = new ClassModel('Foo');

        $classFile->setClass($class);

        $property = new PropertyModel('bar');
        $class->addProperty($property);

        $constructor = new ClassMethodModel('__construct');
        $class->addMethod($constructor);

        $className = ClassName::fromString(ClassName::class);

        $constructor->addStatement(
            $property->assignTo(
                $className
                    ->toNewNode([
                        new ClassConstValue(PropertyModel::class, 'class'),
                    ])
            )
        );

        // $this->bar = new ClassName(PropertyModel::class)

        $code = $classFile->getSourceCode();

        self::assertStringContainsString('$this->bar = new ClassName(PropertyModel::class)', $code);

        $statements = $constructor->getStatements();
        $lhs = $statements[0];

        $rhs = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    'bar'
                ),
                new Node\Expr\New_(
                    new Node\Name('ClassName'),
                    [
                        new Node\Arg(
                            new Node\Expr\ClassConstFetch(
                                new Node\Name('PropertyModel'),
                                'class'
                            )
                        ),
                    ]
                )
            )
        );

        $result = AbstractNodeComparer::compareNodes($lhs, $rhs);

        $this->assertTrue($result);
    }
}

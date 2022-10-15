<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClosureFile;
use SoureCode\PhpObjectModel\Model\ClosureModel;
use SoureCode\PhpObjectModel\Model\ParameterModel;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleAInterface;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleBInterface;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\IntegerType;
use SoureCode\PhpObjectModel\Type\IntersectionType;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\Type\UnionType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

class ClosureModelTest extends TestCase
{
    private ?ClosureFile $file = null;

    private ?ClosureModel $closure = null;

    public function setUp(): void
    {
        $this->file = new ClosureFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleClosureA.php'));
        $this->closure = $this->file->getClosure();
    }

    public function tearDown(): void
    {
        $this->file = null;
        $this->closure = null;
    }

    public function testGetSetReturnType(): void
    {
        $returnType = $this->closure->getReturnType();

        self::assertTrue($returnType->isNullable());
        self::assertSame(StringType::class, $returnType::class);

        $this->closure->setReturnType(new IntegerType());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString(': int', $code);
    }

    public function testSetReturnTypeAddUse(): void
    {
        $this->closure->setReturnType(new ClassType(NamespaceName::class));

        $code = $this->file->getSourceCode();

        self::assertStringContainsString(': NamespaceName', $code);
        self::assertStringContainsString(sprintf('use %s;', NamespaceName::class), $code);
    }

    public function testSetReturnTypeAddUseUnion(): void
    {
        $this->closure->setReturnType(new UnionType([new IntegerType(), new StringType(), new ClassType(ClassName::class)]));

        $code = $this->file->getSourceCode();

        self::assertStringContainsString(': int|string|ClassName', $code);
        self::assertStringContainsString(sprintf('use %s;', ClassName::class), $code);
    }

    public function testSetReturnTypeAddUseIntersection(): void
    {
        $this->closure->setReturnType(new IntersectionType([new ClassType(NamespaceName::class), new ClassType(ClassName::class)]));

        $code = $this->file->getSourceCode();

        self::assertStringContainsString(': NamespaceName&ClassName', $code);
        self::assertStringContainsString(sprintf('use %s;', ClassName::class), $code);
    }

    public function testGetSetAddRemoveParams(): void
    {
        self::assertCount(2, $this->closure->getParameters());
        self::assertSame(ExampleBInterface::class, $this->closure->getParameter('a')->getType()->getClassName()->getName());
        self::assertSame(ExampleAInterface::class, $this->closure->getParameter('b')->getType()->getClassName()->getName());

        self::assertTrue($this->closure->hasParameter('a'));
        self::assertTrue($this->closure->hasParameter('b'));
        self::assertFalse($this->closure->hasParameter('foo'));
        self::assertFalse($this->closure->hasParameter('bar'));
        self::assertFalse($this->closure->hasParameter('baz'));

        $this->closure->setParameters([
            new ParameterModel('foo'),
            new ParameterModel('dolor'),
            new ParameterModel('bar'),
        ]);

        $this->closure->addParameter(new ParameterModel('baz'));

        self::assertTrue($this->closure->hasParameter('foo'));
        self::assertTrue($this->closure->hasParameter('bar'));
        self::assertTrue($this->closure->hasParameter('baz'));
        self::assertTrue($this->closure->hasParameter('dolor'));
        self::assertFalse($this->closure->hasParameter('ab'));
        self::assertFalse($this->closure->hasParameter('b'));

        $this->closure->removeParameter('dolor');

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('function ($foo, $bar, $baz)', $code);
    }

    public function testGetSetAddRemoveStatements()
    {
        $statements = $this->closure->getStatements();

        self::assertCount(3, $statements);
        self::assertSame('Stmt_Expression', $statements[0]->getType());
        self::assertSame('Stmt_If', $statements[1]->getType());
        self::assertSame('Stmt_Return', $statements[2]->getType());

        $this->closure->removeStatement($statements[1]);

        $code = $this->file->getSourceCode();
        $statements = $this->closure->getStatements();

        self::assertCount(2, $statements);
        self::assertSame('Stmt_Expression', $statements[0]->getType());
        self::assertSame('Stmt_Return', $statements[1]->getType());

        self::assertStringNotContainsString('str_contains', $code);

        $this->closure->setStatements([
            new Node\Stmt\Return_(
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable('a'),
                    'bar'
                )
            ),
        ]);

        $statements = $this->closure->getStatements();
        self::assertCount(1, $statements);

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('return $a->bar();', $code);
    }
}

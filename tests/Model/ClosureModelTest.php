<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClosureFile;
use SoureCode\PhpObjectModel\Model\ClosureModel;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Type\IntegerType;
use SoureCode\PhpObjectModel\Type\IntersectionType;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\Type\UnionType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;
use SoureCode\PhpObjectModel\ValueObject\NamespaceItem;

class ClosureModelTest extends TestCase
{
    private ?ClosureFile $file = null;

    private ?ClosureModel $closure = null;

    public function setUp(): void
    {
        $this->file = new ClosureFile(__DIR__ . '/../Fixtures/ExampleClosureA.php');
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
        $this->closure->setReturnType(new ClassType(NamespaceItem::class));

        $code = $this->file->getSourceCode();

        self::assertStringContainsString(': NamespaceItem', $code);
        self::assertStringContainsString(sprintf('use %s;', NamespaceItem::class), $code);
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
        $this->closure->setReturnType(new IntersectionType([new ClassType(NamespaceItem::class), new ClassType(ClassName::class)]));

        $code = $this->file->getSourceCode();

        self::assertStringContainsString(': NamespaceItem&ClassName', $code);
        self::assertStringContainsString(sprintf('use %s;', ClassName::class), $code);
    }

    public function testGetSetAddRemoveParams(): void
    {
        self::assertCount(2, $this->closure->getParams());
        self::assertSame('Param', $this->closure->getParam('a')->getType());
        self::assertSame('Param', $this->closure->getParam('b')->getType());
        self::assertSame('ExampleBInterface', (string) $this->closure->getParam('a')->type);
        self::assertSame('ExampleAInterface', (string) $this->closure->getParam('b')->type);

        self::assertTrue($this->closure->hasParam('a'));
        self::assertTrue($this->closure->hasParam('b'));
        self::assertFalse($this->closure->hasParam('foo'));
        self::assertFalse($this->closure->hasParam('bar'));
        self::assertFalse($this->closure->hasParam('baz'));

        $this->closure->setParams([
            new Node\Param(new Node\Expr\Variable('foo')),
            new Node\Param(new Node\Expr\Variable('dolor')),
            new Node\Param(new Node\Expr\Variable('bar')),
        ]);

        $this->closure->addParam(new Node\Param(new Node\Expr\Variable('baz')));

        self::assertTrue($this->closure->hasParam('foo'));
        self::assertTrue($this->closure->hasParam('bar'));
        self::assertTrue($this->closure->hasParam('baz'));
        self::assertTrue($this->closure->hasParam('dolor'));
        self::assertFalse($this->closure->hasParam('ab'));
        self::assertFalse($this->closure->hasParam('b'));

        $this->closure->removeParam('dolor');

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

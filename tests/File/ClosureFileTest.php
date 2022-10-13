<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\File;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClosureFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\ClosureModel;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class ClosureFileTest extends TestCase
{
    public function testGetSetClass(): void
    {
        $file = new ClosureFile(__DIR__ . '/../Fixtures/ExampleClosureA.php');
        $closure = $file->getClosure();
        $code = $file->getSourceCode();
        $returnType = $closure->getReturnType();

        self::assertSame(StringType::class, $returnType::class);
        self::assertTrue($returnType->isNullable());
        self::assertStringContainsString(': ?string', $code);

        $file->setClosure(new ClosureModel());

        $code = $file->getSourceCode();

        self::assertStringContainsString(': void', $code);
        self::assertStringNotContainsString(': ?string', $code);
    }

    public function testAddUse()
    {
        $file = new ClosureFile(__DIR__ . '/../Fixtures/ExampleClosureA.php');

        $file->addUse(ClassName::fromString(ClassModel::class));

        $code = $file->getSourceCode();

        self::assertStringContainsString(sprintf('use %s;', ClassModel::class), $code);
    }
}

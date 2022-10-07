<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\File;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClosureFile;
use SoureCode\PhpObjectModel\Model\ClosureModel;

class ClosureFileTest extends TestCase
{
    public function testGetSetClass(): void
    {
        $file = new ClosureFile(__DIR__.'/../Fixtures/ExampleClosureA.php');
        $closure = $file->getClosure();
        $code = $file->getSourceCode();

        self::assertSame('NullableType', $closure->getReturnType()->getType());
        self::assertStringContainsString(': ?string', $code);

        $file->setClosure(new ClosureModel());

        $code = $file->getSourceCode();

        self::assertStringContainsString(': void', $code);
        self::assertStringNotContainsString(': ?string', $code);
    }
}

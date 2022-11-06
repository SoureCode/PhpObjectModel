<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\File;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\InterfaceFile;
use SoureCode\PhpObjectModel\Model\InterfaceModel;

class InterfaceFileTest extends TestCase
{
    public function testGetSetInterface(): void
    {
        $file = new InterfaceFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleAInterface.php'));
        $interface = $file->getInterface();
        $code = $file->getSourceCode();

        self::assertSame('ExampleAInterface', $interface->getName()->getShortName());
        self::assertStringContainsString('interface ExampleAInterface', $code);
        self::assertStringNotContainsString('interface Bar', $code);

        $file->setInterface(new InterfaceModel('Bar'));

        $code = $file->getSourceCode();

        self::assertStringContainsString('interface Bar', $code);
        self::assertStringNotContainsString('interface ExampleAInterface', $code);
    }
}

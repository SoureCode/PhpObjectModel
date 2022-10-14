<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\ValueObject\AbstractNamespace;

class AbstractNamespaceTest extends TestCase
{
    public function testGetNameReturnsTheFqn(): void
    {
        // Arrange
        $namespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };

        // Act
        $result = $namespace->getName();

        // Assert
        self::assertSame('Foo\\Lorem\\Bar', $result);
    }

    public function testGetShortNameReturnsTheShortName(): void
    {
        // Arrange
        $namespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };

        // Act
        $result = $namespace->getShortName();

        // Assert
        self::assertSame('Bar', $result);
    }

    public function testLengthReturnsTheLength(): void
    {
        // Arrange
        $namespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };

        // Act
        $result = $namespace->length();

        // Assert
        self::assertSame(3, $result);
    }

    public function testIsSameReturnsTrueWhenTheNamesAreEqual(): void
    {
        // Arrange
        $namespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };
        $otherNamespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };

        // Act
        $result = $namespace->isSame($otherNamespace);

        // Assert
        self::assertTrue($result);
    }

    public function testIsSameReturnsFalseWhenTheNamesAreNotEqual(): void
    {
        // Arrange
        $namespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };

        $otherNamespace = new class('Foo\\Lorem\\Baz') extends AbstractNamespace {
        };

        // Act
        $result = $namespace->isSame($otherNamespace);

        // Assert
        self::assertFalse($result);
    }

    public function testToStringReturnsTheName(): void
    {
        // Arrange
        $namespace = new class('Foo\\Lorem\\Bar') extends AbstractNamespace {
        };

        // Act
        $result = (string) $namespace;

        // Assert
        self::assertSame('Foo\\Lorem\\Bar', $result);
    }
}

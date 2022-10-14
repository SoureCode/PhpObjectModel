<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\ValueObject\AbstractNamespaceName;

class AbstractNamespaceNameTest extends TestCase
{
    public function testGetNamespaceRelativeToReturnsTheRelativeNamespace(): void
    {
        // Arrange
        $namespaceA = new class('Foo\\Lorem\\Bar') extends AbstractNamespaceName {
        };
        $namespaceB = new class('Foo\\Lorem\\Bar\\Baz') extends AbstractNamespaceName {
        };

        // Act
        $result = $namespaceA->getNamespaceRelativeTo($namespaceB);

        // Assert
        self::assertSame('Baz', $result->getName());
    }

    public function testGetLongestCommonNamespaceReturnsTheLongestCommonNamespace(): void
    {
        // Arrange
        $namespaceA = new class('Foo\\Lorem\\Ipsum') extends AbstractNamespaceName {
        };
        $namespaceB = new class('Foo\\Lorem\\Bar\\Baz') extends AbstractNamespaceName {
        };

        // Act
        $result = $namespaceA->getLongestCommonNamespace($namespaceB);

        // Assert
        self::assertSame('Foo\\Lorem', $result->getName());
    }

    public function testGetLongestCommonNamespaceReturnsTheLongestCommonNamespaceWhenTheNamesAreEqual(): void
    {
        // Arrange
        $namespaceA = new class('Foo\\Lorem\\Ipsum') extends AbstractNamespaceName {
        };
        $namespaceB = new class('Foo\\Lorem\\Ipsum') extends AbstractNamespaceName {
        };

        // Act
        $result = $namespaceA->getLongestCommonNamespace($namespaceB);

        // Assert
        self::assertSame('Foo\\Lorem\\Ipsum', $result->getName());
    }

    public function testGetLongestCommonNamespaceReturnsNullWhenNoCommonNamespaceExists(): void
    {
        // Arrange
        $namespaceA = new class('Foo\\Lorem\\Ipsum') extends AbstractNamespaceName {
        };
        $namespaceB = new class('Bar\\Lorem\\Ipsum') extends AbstractNamespaceName {
        };

        // Act
        $result = $namespaceA->getLongestCommonNamespace($namespaceB);

        // Assert
        self::assertNull($result);
    }
}

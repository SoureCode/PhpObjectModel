<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Printer;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
final class PrettyPrinter extends Standard
{
    /**
     * Overridden to change coding standards.
     *
     * Before:
     *      public function getFoo() : string
     *
     * After
     *      public function getFoo(): string
     *
     * @see https://github.com/symfony/maker-bundle/blob/v1.47.0/src/Util/PrettyPrinter.php
     */
    protected function pStmt_ClassMethod(Node\Stmt\ClassMethod $node): string
    {
        /**
         * @var string $classMethod
         */
        $classMethod = parent::pStmt_ClassMethod($node);

        if ($node->returnType) {
            $classMethod = str_replace(') :', '):', $classMethod);
        }

        return $classMethod;
    }
}

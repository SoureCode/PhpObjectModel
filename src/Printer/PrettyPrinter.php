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

    protected function pExpr_MethodCall(Node\Expr\MethodCall $node): string
    {
        if ($node->var instanceof Node\Expr\MethodCall) {
            /**
             * @var string $base
             */
            $base = $this->pDereferenceLhs($node->var);
            /**
             * @var string $property
             */
            $property = $this->pObjectProperty($node->name);
            /**
             * @var string $args
             */
            $args = $this->pMaybeMultiline($node->args);

            return implode('', [
                $base,
                preg_match('/\n(\s+)-/', $base, $matches) ? PHP_EOL . $matches[1] : $this->nl,
                '->',
                $property,
                '(',
                $args,
                ')',
            ]);
        }

        /**
         * @var string $val
         */
        $val = parent::pExpr_MethodCall($node);

        return $val;
    }
}

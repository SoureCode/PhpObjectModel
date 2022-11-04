<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ClassMethodNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Stmt\ClassMethod && $rhs instanceof Node\Stmt\ClassMethod) {
            // params
            if (!AbstractNodeComparer::compareNodes($lhs->params, $rhs->params)) {
                return false;
            }

            // body
            if (!AbstractNodeComparer::compareNodes($lhs->stmts, $rhs->stmts)) {
                return false;
            }

            if ($structural) {
                return true;
            }

            if (!AbstractNodeComparer::compareNodes($lhs->name, $rhs->name)) {
                return false;
            }

            if (!AbstractNodeComparer::compareNodes($lhs->returnType, $rhs->returnType)) {
                return false;
            }

            return true;
        }

        return false;
    }
}

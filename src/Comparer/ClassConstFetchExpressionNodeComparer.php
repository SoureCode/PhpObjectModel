<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ClassConstFetchExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Expr\ClassConstFetch && $rhs instanceof Node\Expr\ClassConstFetch) {
            return AbstractNodeComparer::compareNodes($lhs->name, $rhs->name) &&
                AbstractNodeComparer::compareNodes($lhs->class, $rhs->class);
        }

        return false;
    }
}

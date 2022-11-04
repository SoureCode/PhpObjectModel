<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ExpressionStatementNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Stmt\Expression && $rhs instanceof Node\Stmt\Expression) {
            return AbstractNodeComparer::compareNodes($lhs->expr, $rhs->expr);
        }

        return false;
    }
}

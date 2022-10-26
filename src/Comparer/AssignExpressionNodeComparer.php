<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class AssignExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Expr\Assign && $rhs instanceof Node\Expr\Assign) {
            return AbstractNodeComparer::compareNodes($lhs->expr, $rhs->expr) &&
                AbstractNodeComparer::compareNodes($lhs->var, $rhs->var);
        }

        return false;
    }
}

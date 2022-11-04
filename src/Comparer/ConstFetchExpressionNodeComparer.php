<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ConstFetchExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Expr\ConstFetch && $rhs instanceof Node\Expr\ConstFetch) {
            return AbstractNodeComparer::compareNodes($lhs->name, $rhs->name, $structural);
        }

        return false;
    }
}

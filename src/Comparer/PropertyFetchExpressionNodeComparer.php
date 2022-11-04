<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class PropertyFetchExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Expr\PropertyFetch && $rhs instanceof Node\Expr\PropertyFetch) {
            return AbstractNodeComparer::compareNodes($lhs->name, $rhs->name) &&
                AbstractNodeComparer::compareNodes($lhs->var, $rhs->var);
        }

        return false;
    }
}

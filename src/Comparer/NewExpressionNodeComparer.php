<?php

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class NewExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Expr\New_ && $rhs instanceof Node\Expr\New_) {
            return AbstractNodeComparer::compareNodes($lhs->class, $rhs->class) &&
                AbstractNodeComparer::compareNodes($lhs->args, $rhs->args);
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class VariableExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Expr\Variable && $rhs instanceof Node\Expr\Variable) {
            if (is_string($lhs->name) && is_string($rhs->name)) {
                return $lhs->name === $rhs->name;
            }

            if ($lhs->name instanceof Node\Expr && $rhs->name instanceof Node\Expr) {
                return AbstractNodeComparer::compareNodes($lhs->name, $rhs->name);
            }
        }

        return false;
    }
}

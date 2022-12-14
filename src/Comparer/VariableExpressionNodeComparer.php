<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class VariableExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Expr\Variable && $rhs instanceof Node\Expr\Variable) {
            if (is_string($lhs->name) && is_string($rhs->name)) {
                if ($structural) {
                    return true;
                }

                return $lhs->name === $rhs->name;
            }

            if ($lhs->name instanceof Node\Expr && $rhs->name instanceof Node\Expr) {
                return self::compareNodes($lhs->name, $rhs->name, $structural);
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class BinaryOpExpressionNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Expr\BinaryOp && $rhs instanceof Node\Expr\BinaryOp) {
            if (!self::compareNodes($lhs->left, $rhs->left, $structural)) {
                return false;
            }

            if (!self::compareNodes($lhs->right, $rhs->right, $structural)) {
                return false;
            }

            return true;
        }

        return false;
    }
}

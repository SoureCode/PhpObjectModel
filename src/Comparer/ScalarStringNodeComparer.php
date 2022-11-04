<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ScalarStringNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Scalar\String_ && $rhs instanceof Node\Scalar\String_) {
            if ($structural) {
                return true;
            }

            return $lhs->value === $rhs->value;
        }

        return false;
    }
}

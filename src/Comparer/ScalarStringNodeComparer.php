<?php

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ScalarStringNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Scalar\String_ && $rhs instanceof Node\Scalar\String_) {
            return $lhs->value === $rhs->value;
        }

        return false;
    }
}

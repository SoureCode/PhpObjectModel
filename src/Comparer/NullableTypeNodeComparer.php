<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class NullableTypeNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\NullableType && $rhs instanceof Node\NullableType) {
            return self::compareNodes($lhs->type, $rhs->type, $structural);
        }

        return true;
    }
}

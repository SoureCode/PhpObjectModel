<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class UnionTypeNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\UnionType && $rhs instanceof Node\UnionType) {
            return self::compareNodes(self::sortNodes($lhs->types), self::sortNodes($rhs->types));
        }

        return true;
    }
}

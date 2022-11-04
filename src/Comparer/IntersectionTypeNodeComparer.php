<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class IntersectionTypeNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\IntersectionType && $rhs instanceof Node\IntersectionType) {
            return self::compareNodes(self::sortNodes($lhs->types), self::sortNodes($rhs->types), $structural);
        }

        return true;
    }
}

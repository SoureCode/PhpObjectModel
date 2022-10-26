<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class IdentifierNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Identifier && $rhs instanceof Node\Identifier) {
            return $lhs->toString() === $rhs->toString();
        }

        return false;
    }
}

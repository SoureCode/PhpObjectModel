<?php

namespace SoureCode\PhpObjectModel\Comparer;

use LogicException;
use PhpParser\Node;

class NameNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Name && $rhs instanceof Node\Name) {
            return $lhs->toString() === $rhs->toString();
        }

        return false;
    }
}

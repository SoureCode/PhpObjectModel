<?php

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ArgNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs): bool
    {
        if ($lhs instanceof Node\Arg && $rhs instanceof Node\Arg) {
            if ($lhs->byRef !== $rhs->byRef) {
                return false;
            }

            if ($lhs->unpack !== $rhs->unpack) {
                return false;
            }

            if (null !== $lhs->name && null !== $rhs->name) {
                if (!IdentifierNodeComparer::compare($lhs->name, $rhs->name)) {
                    return false;
                }
            }

            if (null === $lhs->name && null !== $rhs->name) {
                return false;
            }

            if (null !== $lhs->name && null === $rhs->name) {
                return false;
            }

            return self::compareNodes($lhs->value, $rhs->value);
        }

        return false;
    }
}

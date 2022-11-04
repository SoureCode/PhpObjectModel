<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ArgNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Arg && $rhs instanceof Node\Arg) {
            if ($lhs->byRef !== $rhs->byRef) {
                return false;
            }

            if ($lhs->unpack !== $rhs->unpack) {
                return false;
            }

            // @todo is the value part of the structural comparison?
            if (!self::compareNodes($lhs->value, $rhs->value, $structural)) {
                return false;
            }

            if ($structural) {
                return true;
            }

            if (!self::compareNodes($lhs->name, $rhs->name, $structural)) {
                return false;
            }

            return true;
        }

        return false;
    }
}

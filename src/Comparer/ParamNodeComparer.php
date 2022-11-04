<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

class ParamNodeComparer extends AbstractNodeComparer
{
    public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool
    {
        if ($lhs instanceof Node\Param && $rhs instanceof Node\Param) {
            if ($lhs->byRef !== $rhs->byRef) {
                return false;
            }

            if ($lhs->variadic !== $rhs->variadic) {
                return false;
            }

            if (!self::compareNodes($lhs->type, $rhs->type)) {
                return false;
            }

            // @todo is the default value part of the structural comparison?
            if (!self::compareNodes($lhs->default, $rhs->default)) {
                return false;
            }

            if ($structural) {
                return true;
            }

            if (!self::compareNodes($lhs->var, $rhs->var, $structural)) {
                return false;
            }

            return true;
        }

        return false;
    }
}

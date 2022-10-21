<?php

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

abstract class AbstractNodeComparer
{
    public static abstract function compare(Node $lhs, Node $rhs): bool;

    public static function compareNodes(Node|array $lhs, Node|array $rhs): bool
    {
        $lhs = is_array($lhs) ? $lhs : [$lhs];
        $rhs = is_array($rhs) ? $rhs : [$rhs];


        if (count($lhs) !== count($rhs)) {
            return false;
        }

        foreach ($lhs as $key => $node) {
            $comparer = self::getComparer($lhs[$key], $rhs[$key]);

            if (null === $comparer) {
                return false;
            }

            if (!$comparer::compare($lhs[$key], $rhs[$key])) {
                return false;
            }
        }

        return true;
    }

    public static function getComparer(Node $lhs, Node $rhs): ?self
    {
        $lhsClass = get_class($lhs);
        $rhsClass = get_class($rhs);

        if ($lhsClass !== $rhsClass) {
            return null;
        }

        return self::getComparerForNode($lhs);
    }

    private static function getComparerForNode(Node $lhs): ?self
    {
        $class = get_class($lhs);

        return match ($class) {
            Node\Name::class => new NameNodeComparer(),
            Node\Identifier::class => new IdentifierNodeComparer(),
            Node\Arg::class => new ArgNodeComparer(),
            Node\Expr\Assign::class => new AssignExpressionNodeComparer(),
            Node\Expr\PropertyFetch::class => new PropertyFetchExpressionNodeComparer(),
            Node\Expr\Variable::class => new VariableExpressionNodeComparer(),
            Node\Expr\New_::class => new NewExpressionNodeComparer(),
            Node\Stmt\Expression::class => new ExpressionStatementNodeComparer(),
            Node\Scalar\String_::class => new ScalarStringNodeComparer(),
            default => null,
        };
    }
}

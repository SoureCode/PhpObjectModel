<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Comparer;

use PhpParser\Node;

abstract class AbstractNodeComparer
{
    abstract public static function compare(Node $lhs, Node $rhs, bool $structural = false): bool;

    /**
     * @param Node|Node[]|null $lhs
     * @param Node|Node[]|null $rhs
     */
    public static function compareNodes(Node|array|null $lhs, Node|array|null $rhs, bool $structural = false): bool
    {
        $lhs = is_array($lhs) ? $lhs : [$lhs];
        $rhs = is_array($rhs) ? $rhs : [$rhs];

        $lhs = array_filter($lhs, static fn ($node) => null !== $node);
        $rhs = array_filter($rhs, static fn ($node) => null !== $node);

        if (count($lhs) !== count($rhs)) {
            return false;
        }

        foreach ($lhs as $index => $_) {
            $comparer = self::getComparer($lhs[$index], $rhs[$index]);

            if (null === $comparer) {
                return false;
            }

            if (!$comparer::compare($lhs[$index], $rhs[$index], $structural)) {
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

        if ($lhs instanceof Node\Expr\BinaryOp) {
            return new BinaryOpExpressionNodeComparer();
        }

        return match ($class) {
            Node\Name::class => new NameNodeComparer(),
            Node\Identifier::class => new IdentifierNodeComparer(),
            Node\Arg::class => new ArgNodeComparer(),
            Node\Param::class => new ParamNodeComparer(),
            Node\UnionType::class => new UnionTypeNodeComparer(),
            Node\NullableType::class => new NullableTypeNodeComparer(),
            Node\IntersectionType::class => new IntersectionTypeNodeComparer(),
            Node\Expr\Assign::class => new AssignExpressionNodeComparer(),
            Node\Expr\PropertyFetch::class => new PropertyFetchExpressionNodeComparer(),
            Node\Expr\Variable::class => new VariableExpressionNodeComparer(),
            Node\Expr\New_::class => new NewExpressionNodeComparer(),
            Node\Expr\ClassConstFetch::class => new ClassConstFetchExpressionNodeComparer(),
            Node\Expr\ConstFetch::class => new ConstFetchExpressionNodeComparer(),
            Node\Stmt\ClassMethod::class => new ClassMethodNodeComparer(),
            Node\Stmt\Expression::class => new ExpressionStatementNodeComparer(),
            Node\Scalar\String_::class => new ScalarStringNodeComparer(),
            default => null,
        };
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    protected static function sortNodes(array $nodes): array
    {
        usort($nodes, static function ($a, $b) {
            if ($a instanceof Node\Identifier && $b instanceof Node\Identifier) {
                return $a->name <=> $b->name;
            }

            if ($a instanceof Node\Name && $b instanceof Node\Name) {
                return $a->toString() <=> $b->toString();
            }

            return 0;
        });

        return $nodes;
    }
}

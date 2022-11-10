<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

/**
 * @template-covariant T of Node\Expr
 */
interface ValueInterface
{
    /**
     * @psalm-return T
     */
    public function getNode(): Node\Expr;

    public function toArgument(): Node\Arg;
}

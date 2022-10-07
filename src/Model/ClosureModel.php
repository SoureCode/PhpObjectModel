<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

/**
 * @extends AbstractFunctionLikeModel<Node\Expr\Closure>
 */
class ClosureModel extends AbstractFunctionLikeModel
{
    /**
     * @psalm-var Node\Expr\Closure
     */
    protected Node $node;

    public function __construct(Node\Expr\Closure $node = null)
    {
        if (null === $node) {
            $node = new Node\Expr\Closure([
                'returnType' => new Node\Identifier('void'),
            ]);
        }

        parent::__construct($node);
    }
}

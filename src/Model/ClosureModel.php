<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Traits\Attributes;

/**
 * @extends AbstractFunctionLikeModel<Node\Expr\Closure>
 */
class ClosureModel extends AbstractFunctionLikeModel
{
    use Attributes;

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

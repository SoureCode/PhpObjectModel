<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

/**
 * @extends AbstractFunctionLikeModel<Node\Stmt\ClassMethod>
 */
class ClassMethodModel extends AbstractFunctionLikeModel
{
    /**
     * @psalm-var Node\Stmt\ClassMethod
     */
    protected Node $node;

    public function __construct(Node\Stmt\ClassMethod|string $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $node = new Node\Stmt\ClassMethod($nodeOrName, [
                'returnType' => new Node\Identifier('void'),
            ]);
        } else {
            $node = $nodeOrName;
        }

        parent::__construct($node);
    }

    public function isStatic(): bool
    {
        return $this->node->isStatic();
    }

    public function setStatic(bool $static): void
    {
        if ($static) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_STATIC;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_STATIC;
        }
    }

    public function isAbstract(): bool
    {
        return $this->node->isAbstract();
    }

    public function setAbstract(bool $abstract): void
    {
        if ($abstract) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_ABSTRACT;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_ABSTRACT;
        }
    }
}

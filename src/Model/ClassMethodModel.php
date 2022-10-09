<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

/**
 * @extends AbstractFunctionLikeModel<Node\Stmt\ClassMethod>
 */
class ClassMethodModel extends AbstractFunctionLikeModel
{
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

    public function getName(): string
    {
        return $this->node->name->name;
    }

    public function isPrivate(): bool
    {
        return $this->node->isPrivate();
    }

    public function isProtected(): bool
    {
        return $this->node->isProtected();
    }

    public function isPublic(): bool
    {
        return $this->node->isPublic();
    }

    public function setName(string $name): void
    {
        $this->node->name = new Node\Identifier($name);
    }

    public function isStatic(): bool
    {
        return $this->node->isStatic();
    }

    public function setPrivate(): void
    {
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;
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

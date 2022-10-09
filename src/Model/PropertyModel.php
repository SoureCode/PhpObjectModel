<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

/**
 * @extends AbstractModel<Node\Stmt\Property>
 */
class PropertyModel extends AbstractModel
{
    /**
     * @psalm-var Node\Stmt\Property
     */
    protected Node $node;

    public function __construct(Node\Stmt\Property|string $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $node = new Node\Stmt\Property(Class_::MODIFIER_PRIVATE, [
                new Node\Stmt\PropertyProperty($nodeOrName),
            ]);
        } else {
            $node = $nodeOrName;
        }

        parent::__construct($node);
    }

    public function getName(): string
    {
        return $this->node->props[0]->name->name;
    }

    public function setName(string $name): void
    {
        $this->node->props[0]->name = new Node\VarLikeIdentifier($name);
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
        return (bool) ($this->node->flags & Node\Stmt\Class_::MODIFIER_ABSTRACT);
    }

    public function setAbstract(bool $abstract): void
    {
        if ($abstract) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_ABSTRACT;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_ABSTRACT;
        }
    }

    public function isPublic(): bool
    {
        return $this->node->isPublic();
    }

    public function setPublic(): void
    {
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->node->isPrivate();
    }

    public function setPrivate(): void
    {
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PRIVATE;
    }

    public function isProtected(): bool
    {
        return $this->node->isProtected();
    }

    public function setProtected(): void
    {
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PROTECTED;
    }

    public function isReadonly(): bool
    {
        return $this->node->isReadonly();
    }

    public function setReadonly(bool $readonly): void
    {
        if ($readonly) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_READONLY;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_READONLY;
        }
    }
}

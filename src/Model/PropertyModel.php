<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use SoureCode\PhpObjectModel\Traits\Attributes;
use SoureCode\PhpObjectModel\Type\AbstractType;

/**
 * @extends AbstractModel<Node\Stmt\Property>
 */
class PropertyModel extends AbstractModel
{
    use Attributes;

    public function __construct(Node\Stmt\Property|string $nodeOrName, AbstractType|string|null $type = null)
    {
        if (is_string($nodeOrName)) {
            $node = new Node\Stmt\Property(Class_::MODIFIER_PRIVATE, [
                new Node\Stmt\PropertyProperty($nodeOrName),
            ]);
        } else {
            $node = $nodeOrName;
        }

        parent::__construct($node);

        if (null !== $type) {
            $this->setType($type);
        }
    }

    public function getName(): string
    {
        return $this->node->props[0]->name->name;
    }

    public function getType(): ?AbstractType
    {
        if (null === $this->node->type) {
            return null;
        }

        return AbstractType::fromNode($this->node->type);
    }

    public function setType(AbstractType|string|null $type): self
    {
        if (null === $type) {
            $this->node->type = null;
        } else {
            $type = is_string($type) ? AbstractType::fromString($type) : $type;
            $this->node->type = $type->getNode();

            $this->importTypes();
        }

        return $this;
    }

    public function importTypes(): self
    {
        if ($this->file && null !== $this->node->type) {
            $type = $this->node->type;
            $this->node->type = $this->file->resolveType(AbstractType::fromNode($type)) ?? $type;
        }

        foreach ($this->getAttributes() as $attribute) {
            $attribute->importTypes();
        }

        return $this;
    }

    public function setName(string $name): self
    {
        $this->node->props[0]->name = new Node\VarLikeIdentifier($name);

        return $this;
    }

    public function isStatic(): bool
    {
        return $this->node->isStatic();
    }

    public function setStatic(bool $static): self
    {
        if ($static) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_STATIC;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_STATIC;
        }

        return $this;
    }

    public function isAbstract(): bool
    {
        return (bool) ($this->node->flags & Node\Stmt\Class_::MODIFIER_ABSTRACT);
    }

    public function setAbstract(bool $abstract): self
    {
        if ($abstract) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_ABSTRACT;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_ABSTRACT;
        }

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->node->isPublic();
    }

    public function setPublic(): self
    {
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PUBLIC;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->node->isPrivate();
    }

    public function setPrivate(): self
    {
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PRIVATE;

        return $this;
    }

    public function isProtected(): bool
    {
        return $this->node->isProtected();
    }

    public function setProtected(): self
    {
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PROTECTED;

        return $this;
    }

    public function isReadonly(): bool
    {
        return $this->node->isReadonly();
    }

    public function setReadonly(bool $readonly = true): self
    {
        if ($readonly) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_READONLY;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_READONLY;
        }

        return $this;
    }
}

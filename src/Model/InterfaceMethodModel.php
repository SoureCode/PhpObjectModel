<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

class InterfaceMethodModel extends ClassMethodModel
{
    public function getName(): string
    {
        return $this->node->name->name;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isProtected(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return $this->node->isPublic();
    }

    public function setName(string $name): self
    {
        $this->node->name = new Node\Identifier($name);

        return $this;
    }

    public function isStatic(): bool
    {
        return $this->node->isStatic();
    }

    public function setPrivate(): self
    {
        throw new \LogicException('Interface methods can not be private.');
    }

    public function setPublic(): self
    {
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PUBLIC;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PRIVATE;

        return $this;
    }

    public function setProtected(): self
    {
        throw new \LogicException('Interface methods can not be protected.');
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
        return false;
    }

    public function setAbstract(bool $abstract): self
    {
        throw new \LogicException('Interface methods can not be abstract.');
    }

    public function importTypes(): self
    {
        parent::importTypes();

        foreach ($this->getAttributes() as $attribute) {
            $attribute->importTypes();
        }

        return $this;
    }
}

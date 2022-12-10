<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use PhpParser\Node\Arg;
use SoureCode\PhpObjectModel\Traits\Attributes;
use SoureCode\PhpObjectModel\Value\ValueInterface;

/**
 * @extends AbstractFunctionLikeModel<Node\Stmt\ClassMethod>
 */
class ClassMethodModel extends AbstractFunctionLikeModel
{
    use Attributes;

    public function __construct(Node\Stmt\ClassMethod|string $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $returnType = new Node\Identifier('void');

            if ('__construct' === $nodeOrName) {
                $returnType = null;
            }

            $node = new Node\Stmt\ClassMethod($nodeOrName, [
                'returnType' => $returnType,
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
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;

        return $this;
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
        $this->node->flags |= Node\Stmt\Class_::MODIFIER_PROTECTED;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PUBLIC;
        $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_PRIVATE;

        return $this;
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
        return $this->node->isAbstract();
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

    public function importTypes(): self
    {
        parent::importTypes();

        foreach ($this->getAttributes() as $attribute) {
            $attribute->importTypes();
        }

        return $this;
    }

    /**
     * @param array<Arg|ParameterModel|ValueInterface> $arguments
     */
    public function toParentCall(array $arguments): Node\Expr\StaticCall
    {
        return new Node\Expr\StaticCall(
            new Node\Name('parent'),
            new Node\Identifier($this->getName()),
            array_map(static function ($argument) {
                if ($argument instanceof ParameterModel) {
                    return new Node\Arg($argument->toVariable());
                }

                if ($argument instanceof ValueInterface) {
                    return new Node\Arg($argument->getNode());
                }

                return $argument;
            }, $arguments),
        );
    }
}

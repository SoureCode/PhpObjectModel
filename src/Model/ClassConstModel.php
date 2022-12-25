<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;
use SoureCode\PhpObjectModel\Value\NullValue;
use SoureCode\PhpObjectModel\Value\ValueInterface;

/**
 * @extends AbstractModel<Node\Stmt\ClassConst>
 */
class ClassConstModel extends AbstractModel
{
    public function __construct(Node\Stmt\ClassConst|string $node, ValueInterface $value = null)
    {
        if (is_string($node)) {
            if (null === $value) {
                throw new \InvalidArgumentException('Value must be set if node is a string.');
            }

            $node = new Node\Stmt\ClassConst([
                new Node\Const_($node, $value->getNode()),
            ]);
        }

        parent::__construct($node);
    }

    public function getName(): string
    {
        return $this->node->consts[0]->name->name;
    }

    public function getValue(): ValueInterface
    {
        return AbstractValue::fromNode($this->node->consts[0]->value) ?? new NullValue();
    }

    public function setValue(ValueInterface $value = null): self
    {
        $name = $this->getName();

        $this->node = new Node\Stmt\ClassConst([
            new Node\Const_($name, $value?->getNode() ?? (new NullValue())->getNode()),
        ]);

        return $this;
    }

    public function setName(string $name): self
    {
        $value = $this->getValue();

        $this->node->consts = [
            new Node\Const_($name, $value->getNode()),
        ];

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

    public function isFinal(): bool
    {
        return $this->node->isFinal();
    }

    public function setFinal(bool $final): self
    {
        if ($final) {
            $this->node->flags |= Node\Stmt\Class_::MODIFIER_FINAL;
        } else {
            $this->node->flags &= ~Node\Stmt\Class_::MODIFIER_FINAL;
        }

        return $this;
    }

    public function toClassConstFetchNode(bool $self = false): Node\Expr\ClassConstFetch
    {
        $classModel = $this->getClass();
        $className = $classModel?->getName();
        $classNameNode = $className?->toNode();

        if (null === $classNameNode) {
            throw new \InvalidArgumentException('File must be set.');
        }

        if ($self) {
            $classNameNode = new Node\Name('self');
        }

        return new Node\Expr\ClassConstFetch(
            $classNameNode,
            $this->getName(),
        );
    }
}

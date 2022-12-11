<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

/**
 * @extends AbstractModel<Node\Stmt\Declare_>
 */
class DeclareModel extends AbstractModel
{
    public function __construct(Node\Stmt\Declare_ $node = null)
    {
        parent::__construct($node ?? new Node\Stmt\Declare_([]));
    }

    public function setStrictTypes(bool $strictTyping): self
    {
        if ($this->hasStrictTypes()) {
            if ($strictTyping && !$this->isStrictTypes()) {
                $node = $this->getDeclareNode('strict_types');
                $node->value = new Node\Scalar\LNumber(1);
            } else {
                $this->removeDeclareNode('strict_types');
            }
        } elseif ($strictTyping) {
            $this->addDeclareNode(new Node\Stmt\DeclareDeclare('strict_types', new Node\Scalar\LNumber(1)));
        }

        return $this;
    }

    public function hasStrictTypes(): bool
    {
        return $this->hasDeclareNode('strict_types');
    }

    public function setTicks(?int $ticks): self
    {
        if (null === $ticks) {
            if ($this->hasTicks()) {
                $this->removeDeclareNode('ticks');
            }

            return $this;
        }

        if ($this->hasTicks()) {
            $node = $this->getDeclareNode('ticks');

            $node->value = new Node\Scalar\LNumber($ticks);
        } else {
            $this->addDeclareNode(new Node\Stmt\DeclareDeclare('ticks', new Node\Scalar\LNumber($ticks)));
        }

        return $this;
    }

    public function hasTicks(): bool
    {
        return $this->hasDeclareNode('ticks');
    }

    public function getTicks(): int
    {
        if (!$this->hasTicks()) {
            throw new \LogicException('Declare statement has no ticks.');
        }

        /**
         * @var Node\Scalar\LNumber $value
         */
        $value = $this->getDeclareNode('ticks')->value;

        return $value->value;
    }

    public function isStrictTypes(): bool
    {
        if (!$this->hasStrictTypes()) {
            return false;
        }

        $node = $this->getDeclareNode('strict_types');
        /** @var Node\Scalar\LNumber $value */
        $value = $node->value;

        return 1 === $value->value;
    }

    private function hasDeclareNode(string $key): bool
    {
        foreach ($this->node->declares as $declare) {
            if ($declare->key->name === $key) {
                return true;
            }
        }

        return false;
    }

    private function getDeclareNode(string $key): Node\Stmt\DeclareDeclare
    {
        /**
         * @var Node\Stmt\DeclareDeclare|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($key) {
            return $node instanceof Node\Stmt\DeclareDeclare && $key === $node->key->name;
        });

        if (null === $node) {
            throw new \LogicException(sprintf('Declare node with key "%s" not found.', $key));
        }

        return $node;
    }

    public function hasEncoding(): bool
    {
        return $this->hasDeclareNode('encoding');
    }

    public function getEncoding(): string
    {
        if (!$this->hasEncoding()) {
            throw new \LogicException('Declare statement has no encoding.');
        }

        /**
         * @var Node\Scalar\String_ $value
         */
        $value = $this->getDeclareNode('encoding')->value;

        return $value->value;
    }

    public function setEncoding(?string $encoding): self
    {
        if (null === $encoding) {
            if ($this->hasEncoding()) {
                $this->removeDeclareNode('encoding');
            }

            return $this;
        }

        if ($this->hasEncoding()) {
            $node = $this->getDeclareNode('encoding');

            $node->value = new Node\Scalar\String_($encoding);
        } else {
            $this->addDeclareNode(new Node\Stmt\DeclareDeclare('encoding', new Node\Scalar\String_($encoding)));
        }

        return $this;
    }

    private function removeDeclareNode(string $type): self
    {
        $node = $this->getDeclareNode($type);

        $this->manipulator->removeNode($this->node, $node);

        if (null !== $this->file && 0 === count($this->node->declares)) {
            $this->file->setDeclare(null);
        }

        return $this;
    }

    private function addDeclareNode(Node\Stmt\DeclareDeclare $declare): self
    {
        $this->node->declares[] = $declare;

        if (null !== $this->file) {
            $this->file->setDeclare($this);
        }

        return $this;
    }
}

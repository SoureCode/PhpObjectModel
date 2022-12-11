<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @extends AbstractValue<Node\Expr\ClassConstFetch>
 */
class ClassConstValue extends AbstractValue
{
    public function __construct(Node\Expr\ClassConstFetch|ClassName|string $node, string $constName = 'class')
    {
        $node = is_string($node) ? new ClassName($node) : $node;

        if ($node instanceof ClassName) {
            $node = new Node\Expr\ClassConstFetch(
                new Node\Name($node->getName()),
                $constName
            );
        }

        parent::__construct($node);
    }

    public function getClass(): ClassName
    {
        if ($this->node->class instanceof Node\Name) {
            return ClassName::fromNode($this->node->class);
        }

        throw new \InvalidArgumentException('Class must be a Node\Name');
    }

    public function setClass(ClassName|string $className): ClassConstValue
    {
        $className = is_string($className) ? new ClassName($className) : $className;

        $this->node->class = $className->toNode();

        return $this;
    }

    public function setConst(string $name): ClassConstValue
    {
        $this->node->name = new Node\Identifier($name);

        return $this;
    }

    public function getConst(): string
    {
        if ($this->node->name instanceof Node\Identifier) {
            return $this->node->name->name;
        }

        throw new \InvalidArgumentException('Const name is not an identifier');
    }
}

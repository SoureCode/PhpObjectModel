<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use LogicException;
use PhpParser\Node;

/**
 * @extends AbstractValue<Node\Expr\Variable>
 */
class VariableValue extends AbstractValue
{
    public function __construct(Node\Expr\Variable|string $node)
    {
        if (is_string($node)) {
            $node = new Node\Expr\Variable($node);
        }

        parent::__construct($node);
    }

    public function setName(string $name): VariableValue
    {
        $this->node->name = $name;

        return $this;
    }

    public function getName(): string
    {
        if (is_string($this->node->name)) {
            return $this->node->name;
        }

        throw new LogicException('Variable name is not a string.');
    }
}

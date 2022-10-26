<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class ClassType extends AbstractType
{
    public function __construct(ClassName|string $className)
    {
        parent::__construct(
            new Node\Name((string) $className)
        );
    }

    public function getClassName(): ClassName
    {
        /**
         * @var Node\Name $node
         */
        $node = $this->node;

        return ClassName::fromNode($node);
    }

    /**
     * @param AbstractValue[] $args
     */
    public function toNewNode(array $args = []): Node\Expr\New_
    {
        return $this->getClassName()->toNewNode($args);
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Node\NodeManipulator;

/**
 * @psalm-param Node\Name $node
 */
class ClassType extends AbstractType
{
    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className)
    {
        parent::__construct(new Node\Name($className));
    }

    public function getClassName(): string
    {
        /**
         * @var Node\Name $node
         */
        $node = $this->node;

        return NodeManipulator::resolveName($node);
    }
}

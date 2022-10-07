<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;

/**
 * @psalm-template T of Node\Stmt\ClassLike
 *
 * @psalm-extends AbstractModel<T>
 */
abstract class AbstractClassLikeModel extends AbstractModel
{
    /**
     * @psalm-param T $node
     */
    public function __construct(Node\Stmt\ClassLike $node)
    {
        parent::__construct($node);
    }

    public function getName(): string
    {
        return $this->node->name->name;
    }

    public function setName(string $name): void
    {
        $this->node->name = new Node\Identifier($name);
    }
}

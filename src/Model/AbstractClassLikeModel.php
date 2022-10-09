<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use Exception;
use PhpParser\Node;

/**
 * @template T of Node\Stmt\ClassLike
 *
 * @extends AbstractModel<T>
 */
abstract class AbstractClassLikeModel extends AbstractModel
{
    public function getName(): string
    {
        /**
         * @var Node\Stmt\ClassLike $node
         */
        $node = $this->node;

        if (null === $node->name) {
            throw new Exception('Invalid class name.');
        }

        return $node->name->name;
    }

    public function setName(string $name): void
    {
        $this->node->name = new Node\Identifier($name);
    }
}

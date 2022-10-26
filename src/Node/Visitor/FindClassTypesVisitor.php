<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Node\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class FindClassTypesVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node\Name[]
     */
    private array $types = [];

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Node\Expr\ClassConstFetch) {
            $class = $node->class;

            if ($class instanceof Node\Name) {
                $this->addName($class);
            }
        }

        if ($node instanceof Node\Name) {
            $this->addName($node);
        }

        return null;
    }

    /**
     * @return Node\Name[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    protected function addName(Node\Name $node): void
    {
        if (!$node->isSpecialClassName()) {
            $className = ClassName::fromNode($node);

            if ($className->length() > 1) {
                $this->types[] = $node;
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;
use RuntimeException;
use SoureCode\PhpObjectModel\Node\NodeManipulator;

class UnionType extends ComplexType
{
    /**
     * @param (PrimitiveType|ResourceType|ClassType)[] $types
     */
    public function __construct(array $types)
    {
        /**
         * @var (Node\Identifier|Node\Name)[] $nodes
         */
        $nodes = array_map(static fn (PrimitiveType|ResourceType|ClassType $type) => $type->node, $types);

        parent::__construct(new Node\UnionType($nodes));
    }

    public function addType(PrimitiveType|ResourceType|ClassType $type): void
    {
        if ($this->hasType($type)) {
            return;
        }

        /**
         * @var Node\Identifier|Node\Name $typeNode
         */
        $typeNode = $type->getNode();

        /**
         * @var Node\UnionType $node
         */
        $node = $this->node;

        $node->types[] = $typeNode;
    }

    /**
     * @return (PrimitiveType|ResourceType|ClassType)[]
     */
    public function getTypes(): array
    {
        /**
         * @var Node\UnionType $node
         */
        $node = $this->node;
        $types = [];

        foreach ($node->types as $type) {
            $typeNode = self::fromNode($type);

            if ($typeNode instanceof NullType) {
                continue;
            }

            if (
                $typeNode instanceof PrimitiveType
                || $typeNode instanceof ResourceType
                || $typeNode instanceof ClassType
            ) {
                $types[] = $typeNode;
            } else {
                throw new RuntimeException('Union type can only contain primitive, resource or class types.');
            }
        }

        return $types;
    }

    public function hasType(PrimitiveType|ResourceType|ClassType $type): bool
    {
        /**
         * @var Node\Identifier|Node\Name $typeNode
         */
        $typeNode = $type->getNode();
        $typeName = $typeNode instanceof Node\Identifier ? $typeNode->name : NodeManipulator::resolveName($typeNode);

        /**
         * @var Node\UnionType $node
         */
        $node = $this->node;

        foreach ($node->types as $currentTypeNode) {
            $name = $currentTypeNode instanceof Node\Identifier ?
                $currentTypeNode->name : NodeManipulator::resolveName($currentTypeNode);

            if ($typeName === $name) {
                return true;
            }
        }

        return false;
    }

    public function setNullable(bool $nullable): AbstractType
    {
        /**
         * @var Node\UnionType $node
         */
        $node = $this->node;

        if ($nullable) {
            $node->types[] = new Node\Identifier('null');
        } else {
            $node->types = array_filter(
                $node->types,
                static function (Node\Name|Node\Identifier $type) {
                    return !($type instanceof Node\Identifier && 'null' === $type->name);
                }
            );
        }

        return parent::setNullable($nullable);
    }
}

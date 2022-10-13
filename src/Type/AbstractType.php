<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;
use RuntimeException;
use SoureCode\PhpObjectModel\Node\NodeManipulator;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

abstract class AbstractType
{
    protected Node\Name|Node\Identifier|Node\ComplexType $node;

    protected bool $nullable = false;

    protected function __construct(Node\NullableType|Node\Name|Node\Identifier|Node\ComplexType $type)
    {
        if ($type instanceof Node\NullableType) {
            if ($type->type instanceof Node\Identifier) {
                $this->node = new Node\Identifier($type->type->name);
            } else {
                $name = NodeManipulator::resolveName($type->type);
                $this->node = new Node\Name($name);
            }

            $this->nullable = true;
        } elseif ($type instanceof Node\Identifier) {
            $this->node = new Node\Identifier($type->name);
        } elseif ($type instanceof Node\ComplexType) {
            $this->node = $type;
        } else {
            $this->node = new Node\Name($type->parts);
        }
    }

    public static function fromNode(Node\NullableType|Node\Name|Node\Identifier|Node\ComplexType $typeNode): self
    {
        if ($typeNode instanceof Node\UnionType) {
            /**
             * @var (PrimitiveType|ResourceType|ClassType)[] $types
             */
            $types = [];
            $nullable = false;

            foreach ($typeNode->types as $type) {
                if ($type instanceof Node\Identifier && 'null' === $type->name) {
                    $nullable = true;
                    continue;
                }

                $node = self::fromNode($type);

                if ($node instanceof PrimitiveType || $node instanceof ResourceType || $node instanceof ClassType) {
                    $types[] = $node;
                } else {
                    throw new RuntimeException('Union type can only contain primitive, resource or class types.');
                }
            }

            $type = new UnionType($types);
            $type->setNullable($nullable);

            return $type;
        }

        if ($typeNode instanceof Node\IntersectionType) {
            /**
             * @var ClassType[] $types
             */
            $types = [];

            foreach ($typeNode->types as $type) {
                $node = self::fromNode($type);

                if (!($node instanceof ClassType)) {
                    throw new RuntimeException('Intersection type can only contain class types.');
                }

                $types[] = $node;
            }

            return new IntersectionType($types);
        }

        $nullable = false;

        if ($typeNode instanceof Node\NullableType) {
            $nullable = true;
            $typeNode = $typeNode->type;
        } elseif ($typeNode instanceof Node\ComplexType) {
            throw new RuntimeException('Not implemented');
        }

        if ($typeNode instanceof Node\Identifier) {
            $type = self::resolveType($typeNode->name);
        } else {
            $type = new ClassType(ClassName::fromNode($typeNode));
        }

        if (null === $type) {
            throw new RuntimeException(sprintf('Could not resolve type for "%s".', $typeNode::class));
        }

        $type->setNullable($nullable);

        return $type;
    }

    private static function resolveType(string $name): ?AbstractType
    {
        return match ($name) {
            'string' => new StringType(),
            'int' => new IntegerType(),
            'float' => new FloatType(),
            'bool' => new BooleanType(),
            'array' => new ArrayType(),
            'object' => new ObjectType(),
            'mixed' => new MixedType(),
            'null' => new NullType(),
            'void' => new VoidType(),
            'callable' => new CallableType(),
            'iterable' => new IterableType(),
            'resource' => new ResourceType(),
            default => null,
        };
    }

    public function getNode(): Node\Identifier|Node\Name|Node\ComplexType|Node\NullableType
    {
        if ($this->node instanceof Node\ComplexType) {
            return $this->node;
        }

        if ($this->nullable) {
            return new Node\NullableType($this->node);
        }

        return $this->node;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function isNullable(): bool
    {
        if ($this->node instanceof Node\IntersectionType) {
            return false;
        }

        return $this->nullable;
    }
}

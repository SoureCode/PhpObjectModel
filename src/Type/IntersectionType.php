<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Type;

use PhpParser\Node;

class IntersectionType extends ComplexType
{
    /**
     * @var ClassType[]
     */
    public array $types;

    /**
     * @param ClassType[] $types
     */
    public function __construct(array $types)
    {
        $this->setTypes($types);

        /**
         * @var Node\Name[] $nodes
         */
        $nodes = array_map(static fn (ClassType $type) => $type->getNode(), $types);

        parent::__construct(
            new Node\IntersectionType($nodes)
        );
    }

    public function addType(ClassType $type): void
    {
        $this->types[] = $type;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function hasType(ClassType $type): bool
    {
        foreach ($this->types as $currentType) {
            if ($currentType->getClassName()->isSame($type->getClassName())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassType[] $types
     */
    private function setTypes(array $types): void
    {
        $this->types = [];

        foreach ($types as $type) {
            if ($type->isNullable()) {
                throw new \RuntimeException('Intersection type can not contain nullable types.');
            }

            $this->addType($type);
        }
    }

    public function setNullable(bool $nullable): AbstractType
    {
        throw new \RuntimeException('Intersection types can not be nullable.');
    }

    public function isNullable(): bool
    {
        return false;
    }
}

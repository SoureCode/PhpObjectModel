<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Node;
use SoureCode\PhpObjectModel\File\AbstractFile;
use SoureCode\PhpObjectModel\Manipulator\ClassManipulator;

/**
 * @extends AbstractModel<Node\Stmt\Class_>
 */
class ClassModel extends AbstractModel
{
    private ClassManipulator $manipulator;

    public function __construct(AbstractFile $file, Node\Stmt\Class_ $node)
    {
        parent::__construct($file, $node);

        $this->manipulator = new ClassManipulator($node);
    }

    public function getName(): string
    {
        return $this->node->name->name;
    }

    public function setName(string $name): void
    {
        $this->node->name->name = $name;
    }

    // get properties
    public function getProperties(): array
    {
        return $this->manipulator->findNodes(function (Node $node) {
            return $node instanceof Node\Stmt\Property;
        });
    }

    // has property
    public function hasProperty(string $name): bool
    {
        $properties = $this->getProperties();

        foreach ($properties as $property) {
            if ($property->props[0]->name->name === $name) {
                return true;
            }
        }

        return false;
    }

    // get property
    public function getProperty(string $name): Node\Stmt\Property
    {
        $properties = $this->getProperties();

        foreach ($properties as $property) {
            if ($property->props[0]->name->name === $name) {
                return $property;
            }
        }

        throw new \Exception(sprintf('Property "%s" not found.', $name));
    }

    // add property


    // remove property

    // get methods

    // has method
    // get method
    // add method
    // remove method

    // get constants

    // has constant
    // get constant
    // add constant
    // remove constant

    // getTraits

    // usesTrait (has)
    // useTrait (add)
    // removeTrait (remove)

    // getInterfaces
    // implementInterface (add)
    // removeInterface (remove)
    // implementsInterface (has)

    // getParent (get)
    // extend (set)
}

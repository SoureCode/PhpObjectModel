<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use InvalidArgumentException;
use PhpParser\Node;
use SoureCode\PhpObjectModel\File\AbstractFile;
use SoureCode\PhpObjectModel\File\InterfaceFile;
use SoureCode\PhpObjectModel\Traits\Attributes;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

/**
 * @extends AbstractClassLikeModel<Node\Stmt\Interface_>
 */
class InterfaceModel extends AbstractClassLikeModel
{
    use Attributes;

    public function __construct(Node\Stmt\Interface_|string|ClassName $nodeOrName)
    {
        if (is_string($nodeOrName)) {
            $nodeOrName = new ClassName($nodeOrName);
        }

        if ($nodeOrName instanceof ClassName) {
            $nodeOrName = new Node\Stmt\Interface_($nodeOrName->getShortName());
        }

        parent::__construct($nodeOrName);
    }

    public function importTypes(): self
    {
        // re-set attributes
        foreach ($this->getAttributes() as $attribute) {
            $attribute->importTypes();
        }

        // re-set extends
        foreach ($this->getExtends() as $extends) {
            $this->extend($extends);
        }

        // re-set methods
        foreach ($this->getMethods() as $method) {
            $method->importTypes();
        }

        return $this;
    }

    public function setFile(?AbstractFile $file): self
    {
        parent::setFile($file);

        $this->importTypes();

        return $this;
    }

    /**
     * @return ClassName[]
     */
    public function getExtends(): array
    {
        return array_map(static function (Node\Name $node) {
            return ClassName::fromNode($node);
        }, $this->node->extends);
    }

    /**
     * @param InterfaceModel|string|InterfaceFile|ClassName|class-string $name
     *
     * @return $this
     */
    public function extend(InterfaceModel|string|InterfaceFile|ClassName $name): self
    {
        $name = $this->resolveInterfaceClassName($name);

        if ($this->extends($name)) {
            return $this;
        }

        $node = $this->file?->resolveUseName($name) ?? $name->toNode();

        $this->node->extends[] = $node;

        return $this;
    }

    /**
     * @param InterfaceModel|string|InterfaceFile|ClassName|class-string $name
     */
    public function extends(InterfaceModel|string|InterfaceFile|ClassName $name): bool
    {
        $name = $this->resolveInterfaceClassName($name);

        foreach ($this->node->extends as $node) {
            $nodeName = ClassName::fromNode($node);

            if ($nodeName->isSame($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return InterfaceMethodModel[]
     */
    public function getMethods(): array
    {
        /**
         * @var Node\Stmt\ClassMethod[] $methods
         */
        $methods = $this->finder->findInstanceOf($this->node->stmts, Node\Stmt\ClassMethod::class);

        return array_map(function (Node\Stmt\ClassMethod $node) {
            $model = new InterfaceMethodModel($node);
            $model->setFile($this->file);

            return $model;
        }, $methods);
    }

    public function hasMethod(string|InterfaceMethodModel $name): bool
    {
        $name = $name instanceof InterfaceMethodModel ? $name->getName() : $name;
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === $name;
        });

        return null !== $node;
    }

    public function getMethod(string $name): InterfaceMethodModel
    {
        /**
         * @var Node\Stmt\ClassMethod|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $node) use ($name) {
            return $node instanceof Node\Stmt\ClassMethod && $node->name->name === $name;
        });

        if (null === $node) {
            throw new InvalidArgumentException(sprintf('Method "%s" not found.', $name));
        }

        $model = new InterfaceMethodModel($node);
        $model->setFile($this->file);

        return $model;
    }

    public function addMethod(ClassMethodModel|string $method): self
    {
        if (is_string($method)) {
            $method = new ClassMethodModel($method);
        }

        if ($this->hasMethod($method->getName())) {
            throw new InvalidArgumentException(sprintf('Method "%s" already exists.', $method->getName()));
        }

        $node = $method->getNode();

        $targetNode = $this->finder->findLastInstanceOf($this->node, Node\Stmt\ClassMethod::class);

        if ($targetNode) {
            $index = (int) array_search($targetNode, $this->node->stmts, true);

            array_splice(
                $this->node->stmts,
                $index + 1,
                0,
                [$node]
            );
        } else {
            $this->node->stmts[] = $node;
        }

        $method->setFile($this->file);
        $method->importTypes();

        return $this;
    }

    public function removeMethod(string|InterfaceMethodModel $name): self
    {
        $name = $name instanceof InterfaceMethodModel ? $name->getName() : $name;
        $method = $this->getMethod($name);
        $this->manipulator->removeNode($this->node, $method->getNode());

        return $this;
    }
}

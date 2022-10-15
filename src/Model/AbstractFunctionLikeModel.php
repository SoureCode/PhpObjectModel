<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use InvalidArgumentException;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use SoureCode\PhpObjectModel\Type\AbstractType;

/**
 * @template T of Node\Expr\Closure|Node\Stmt\ClassMethod
 *
 * @extends AbstractModel<T>
 */
abstract class AbstractFunctionLikeModel extends AbstractModel
{
    public function getReturnType(): AbstractType|null
    {
        return $this->node->returnType ? AbstractType::fromNode($this->node->returnType) : null;
    }

    public function setReturnType(AbstractType|string|null $returnType): self
    {
        if (null === $returnType) {
            $this->node->returnType = null;

            return $this;
        }

        if (is_string($returnType)) {
            $returnType = AbstractType::fromString($returnType);
        }

        $node = $this->file?->resolveType($returnType);
        $this->node->returnType = $node ?? $returnType->getNode();

        return $this;
    }

    /**
     * @return array<ParameterModel>
     */
    public function getParameters(): array
    {
        return array_map(function (Node\Param $param) {
            $model = new ParameterModel($param);
            $model->setFile($this->file);

            return $model;
        }, $this->node->params);
    }

    /**
     * @psalm-param array<ParameterModel> $params
     */
    public function setParameters(array $params): self
    {
        $this->node->params = [];

        foreach ($params as $param) {
            $this->addParameter($param);
        }

        return $this;
    }

    public function hasParameter(string|ParameterModel $name): bool
    {
        $name = $name instanceof ParameterModel ? $name->getName() : $name;

        /**
         * @var Node\Param|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $param) use ($name): bool {
            if ($param instanceof Node\Param && $param->var instanceof Node\Expr\Variable) {
                return $param->var->name === $name;
            }

            return false;
        });

        return null !== $node;
    }

    public function getParameter(string $name): ParameterModel
    {
        /**
         * @var Node\Param|null $node
         */
        $node = $this->finder->findFirst($this->node, function (Node $param) use ($name): bool {
            if ($param instanceof Node\Param && $param->var instanceof Node\Expr\Variable) {
                return $param->var->name === $name;
            }

            return false;
        });

        if (null === $node) {
            throw new InvalidArgumentException(sprintf('Param "%s" not found.', $name));
        }

        $model = new ParameterModel($node);

        if ($this->file) {
            $model->setFile($this->file);
        }

        return $model;
    }

    public function addParameter(ParameterModel|string $param, AbstractType|string|null $type = null): self
    {
        $param = is_string($param) ? new ParameterModel($param, $type) : $param;

        $this->node->params = [
            ...$this->node->params,
            $param->getNode(),
        ];

        $param->setFile($this->file);

        if (null !== $this->file) {
            $type = $param->getType();

            if (null !== $type) {
                $name = $this->file->resolveType($type);

                if (null !== $name) {
                    $param->setType(AbstractType::fromNode($name));
                }
            }
        }

        return $this;
    }

    public function removeParameter(string|ParameterModel $name): self
    {
        $param = is_string($name) ? $this->getParameter($name) : $name;

        $this->manipulator->removeNode($this->node, $param->getNode());

        $param->setFile(null);

        return $this;
    }

    /**
     * @return array<Node>|null
     */
    public function getStatements(): ?array
    {
        return $this->node->stmts;
    }

    /**
     * @param array<Node> $statements
     */
    public function setStatements(array $statements = []): self
    {
        $builder = new BuilderFactory();
        $method = $builder->method('');

        foreach ($statements as $statement) {
            $method->addStmt($statement);
        }

        $this->node->stmts = $method->getNode()->stmts ?? [];

        return $this;
    }

    public function addStatement(Node $statement): self
    {
        $builder = new BuilderFactory();
        $method = $builder->method('');

        foreach ($this->node->stmts ?? [] as $stmt) {
            $method->addStmt($stmt);
        }

        $method->addStmt($statement);

        $this->node->stmts = $method->getNode()->stmts ?? [];

        return $this;
    }

    public function removeStatement(Node $node): self
    {
        $this->manipulator->removeNode($this->node, $node);

        return $this;
    }
}

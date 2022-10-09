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

    public function setReturnType(?AbstractType $returnType): void
    {
        $this->node->returnType = $returnType ? $returnType->getNode() : null;
    }

    /**
     * @return array<Node\Param>
     */
    public function getParams(): array
    {
        return $this->node->params;
    }

    /**
     * @psalm-param array<Node\Param> $params
     */
    public function setParams(array $params): void
    {
        $this->node->params = array_values($params);
    }

    public function hasParam(string $name): bool
    {
        /**
         * @var Node\Param|null $node
         */
        $node = $this->finder->findFirst($this->getParams(), function (Node $param) use ($name): bool {
            if ($param instanceof Node\Param && $param->var instanceof Node\Expr\Variable) {
                return $param->var->name === $name;
            }

            return false;
        });

        return null !== $node;
    }

    public function getParam(string $name): Node\Param
    {
        /**
         * @var Node\Param|null $node
         */
        $node = $this->finder->findFirst($this->getParams(), function (Node $param) use ($name): bool {
            if ($param instanceof Node\Param && $param->var instanceof Node\Expr\Variable) {
                return $param->var->name === $name;
            }

            return false;
        });

        if (null === $node) {
            throw new InvalidArgumentException(sprintf('Param "%s" not found.', $name));
        }

        return $node;
    }

    public function addParam(Node\Param $param): void
    {
        $params = $this->getParams();

        $params[] = $param;

        $this->setParams($params);
    }

    public function removeParam(string $name): void
    {
        $param = $this->getParam($name);

        $this->manipulator->removeNode($this->node, $param);
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
    public function setStatements(array $statements = []): void
    {
        $builder = new BuilderFactory();
        $method = $builder->method('');

        foreach ($statements as $statement) {
            $method->addStmt($statement);
        }

        $this->node->stmts = $method->getNode()->stmts ?? [];
    }

    public function addStatement(Node $statement): void
    {
        $builder = new BuilderFactory();
        $method = $builder->method('')->addStmt($statement)->getNode();

        $this->node->stmts = $method->stmts ?? [];
    }

    public function removeStatement(Node $node): void
    {
        $this->manipulator->removeNode($this->node, $node);
    }
}

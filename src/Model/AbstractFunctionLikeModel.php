<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Model;

use PhpParser\Builder;
use PhpParser\BuilderHelpers;
use PhpParser\Node;

/**
 * @psalm-template T of Node\FunctionLike
 *
 * @psalm-extends AbstractModel<T>
 */
abstract class AbstractFunctionLikeModel extends AbstractModel
{
    /**
     * @psalm-var Node\FunctionLike
     */
    protected Node $node;

    public function __construct(Node\FunctionLike $node)
    {
        parent::__construct($node);
    }

    public function getReturnType(): Node\Name|Node\Identifier|Node\ComplexType|null
    {
        return $this->node->getReturnType();
    }

    public function setReturnType(Node\Name|Node\Identifier|Node\ComplexType|null $returnType): void
    {
        $this->node->returnType = $returnType;
    }

    /**
     * @return array<Node\Param>
     */
    public function getParams(): array
    {
        return $this->node->params;
    }

    /**
     * @param array<Node\Param> $params
     */
    public function setParams(array $params): void
    {
        $this->node->params = $params;
    }

    public function hasParam(string $name): bool
    {
        /**
         * @var Node\Param|null $node
         */
        $node = $this->finder->findFirst($this->getParams(), function (Node $param) use ($name): bool {
            return $param instanceof Node\Param && $param->var->name === $name;
        });

        return null !== $node;
    }

    public function getParam(string $name): Node\Param
    {
        /**
         * @var Node\Param|null $node
         */
        $node = $this->finder->findFirst($this->getParams(), function (Node $param) use ($name): bool {
            return $param instanceof Node\Param && $param->var->name === $name;
        });

        if (null === $node) {
            throw new \InvalidArgumentException(sprintf('Param "%s" not found.', $name));
        }

        return $node;
    }

    public function addParam(Node\Param $param): void
    {
        $this->node->params[] = $param;
    }

    public function removeParam(string $name): void
    {
        $param = $this->getParam($name);

        $this->manipulator->removeNode($this->node, $param);
    }

    /**
     * @return array<Node\Stmt>|null
     */
    public function getStatements(): ?array
    {
        return $this->node->stmts;
    }

    /**
     * @param array<Node\Stmt>|null $statements
     */
    public function setStatements(?array $statements): void
    {
        $this->node->stmts = $statements;
    }

    public function addStatement(Node\Stmt|Builder $statement): void
    {
        $this->node->stmts[] = BuilderHelpers::normalizeStmt($statement);
    }

    public function removeStatement(Node\Stmt $node): void
    {
        $this->manipulator->removeNode($this->node, $node);
    }
}

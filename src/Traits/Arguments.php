<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Traits;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Model\ArgumentModel;

trait Arguments
{
    public function setArgument(ArgumentModel|Node\Arg $modelOrNode): self
    {
        $argument = $modelOrNode instanceof ArgumentModel ? $modelOrNode->getNode() : $modelOrNode;

        if (null !== $argument->name && $this->hasArgument($argument->name->name)) {
            $oldArgument = $this->getArgument($argument->name->name)->getNode();

            $this->argumentsNode->args = array_map(
                static function (Node\Arg $arg) use ($oldArgument, $argument) {
                    if ($arg === $oldArgument) {
                        return $argument;
                    }

                    return $arg;
                },
                $this->argumentsNode->args
            );

            if ($modelOrNode instanceof ArgumentModel) {
                $modelOrNode->setFile($this->file);
                $modelOrNode->importTypes();
            }

            return $this;
        }

        $this->argumentsNode->args = [...$this->argumentsNode->args, $argument];

        if ($modelOrNode instanceof ArgumentModel) {
            $modelOrNode->setFile($this->file);
            $modelOrNode->importTypes();
        }

        return $this;
    }

    /**
     * @return ArgumentModel[]
     */
    public function getArguments(): array
    {
        return array_map(function (Node\Arg $arg) {
            $model = new ArgumentModel($arg);
            $model->setFile($this->file);

            return $model;
        }, $this->argumentsNode->args);
    }

    /**
     * @param ArgumentModel[] $args
     */
    public function setArguments(array $args): self
    {
        $this->argumentsNode->args = [];

        foreach ($args as $arg) {
            $this->setArgument($arg->getNode());
        }

        return $this;
    }

    public function getArgument(string $name): ArgumentModel
    {
        foreach ($this->argumentsNode->args as $arg) {
            if ($arg->name && $arg->name->name === $name) {
                $model = new ArgumentModel($arg);
                $model->setFile($this->file);

                return $model;
            }
        }

        throw new \LogicException(sprintf('Argument "%s" not found.', $name));
    }

    public function hasArgument(ArgumentModel|Node\Arg|string $modelOrName): bool
    {
        $node = $modelOrName instanceof ArgumentModel ? $modelOrName->getNode() : $modelOrName;

        if (is_string($node)) {
            return null !== $this->finder->findFirst(
                $this->argumentsNode->args,
                fn (Node $arg) => $arg instanceof Node\Arg && $arg->name && $arg->name->name === $node
            );
        }

        return null !== $this->finder->findFirst(
            $this->argumentsNode->args,
            fn (Node $arg) => $arg === $node
        );
    }

    public function removeArgument(ArgumentModel|Node\Arg|string $modelOrName): self
    {
        $node = $modelOrName instanceof ArgumentModel ? $modelOrName->getNode() : $modelOrName;

        if (is_string($node)) {
            $this->argumentsNode->args = array_filter(
                $this->argumentsNode->args,
                static fn (Node\Arg $arg) => $arg->name && $arg->name->name !== $node
            );

            return $this;
        }

        $this->argumentsNode->args = array_filter(
            $this->argumentsNode->args,
            static fn (Node\Arg $arg) => $arg !== $node
        );

        return $this;
    }
}

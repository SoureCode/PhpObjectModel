<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Fixtures;

class ExampleClassA extends AbstractBaseClassA implements ExampleAInterface
{
    public function foo(): void
    {
        throw new \Exception('Not implemented yet.');
    }

    public function bar(): string
    {
        throw new \Exception('Not implemented yet.');
    }
}

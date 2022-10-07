<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Fixtures;

abstract class AbstractBaseClassA implements ExampleBInterface
{
    public function dolor(): void
    {
        throw new \Exception('Not implemented yet.');
    }

    public function ipsum(): string
    {
        throw new \Exception('Not implemented yet.');
    }
}

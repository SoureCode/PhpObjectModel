<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Fixtures;

abstract class AbstractBaseClassB implements ExampleBInterface
{
    public function dolor(): void
    {
        throw new \Exception('Will be implemented soon.');
    }

    public function ipsum(): string
    {
        throw new \Exception('Will be implemented soon.');
    }
}

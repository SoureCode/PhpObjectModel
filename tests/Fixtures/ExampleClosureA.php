<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model\Fixtures;

use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleAInterface;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleBInterface;

return static function (ExampleBInterface $a, ExampleAInterface $b): ?string {
    $c = $a->bar();

    if (str_contains($c, 'foo')) {
        return null;
    }

    return $c;
};

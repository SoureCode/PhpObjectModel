<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Fixtures;

use SoureCode\PhpObjectModel\Node;

class ExampleClassTypes
{
    public string $string1 = 'foo1';
    public int $int1 = 1;
    public float $float1 = 1.1;
    public bool $bool1 = true;
    public array $array1 = [];
    public object $object1;
    public ExampleBInterface $interface1;
    public Node\NodeFinder $relative1;
    public string|bool $union1 = 'foo1';
    public ExampleBInterface&ExampleAInterface $intersection1;

    public ?string $string2 = 'foo2';
    public ?int $int2 = 2;
    public ?float $float2 = 2.2;
    public ?bool $bool2 = false;
    public ?array $array2 = [];
    public ?object $object2;
    public ?ExampleBInterface $interface2;
    public ?Node\NodeFinder $relative2;
    public string|bool|null $union2 = 'foo2';
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Fixtures;

class ExampleClassA extends AbstractBaseClassA implements ExampleAInterface
{
    private static string $staticProperty = 'foo1';
    protected static string $staticProperty2 = 'foo2';
    public static string $staticProperty3 = 'foo3';

    private string $property = 'foo1';
    protected string $property2 = 'foo2';
    public string $property3 = 'foo3';

    private $property4 = 'foo4';
    protected $property5 = 'foo5';
    public $property6 = 'foo6';

    private ?string $property7 = null;
    protected ?string $property8 = "";
    public ?string $property9 = null;

    public function foo(): void
    {
        throw new \Exception('Not implemented yet.');
    }

    public function bar(): string
    {
        throw new \Exception('Not implemented yet.');
    }
}

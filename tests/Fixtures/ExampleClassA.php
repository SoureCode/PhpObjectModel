<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Fixtures;

use SoureCode\PhpObjectModel\Model as Test;
use SoureCode\PhpObjectModel\Model\ClosureModel as CM;
use SoureCode\PhpObjectModel\Model\PropertyModel;

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
    protected ?string $property8 = '';
    public ?string $property9 = null;

    public function foo(): void
    {
        throw new \Exception('Not implemented yet.');
    }

    public function bar(): string
    {
        throw new \Exception('Not implemented yet.');
    }

    public function baz(string $foo, int $bar): string
    {
        return sprintf('%s - %d', $foo, $bar);
    }

    public function qux(string $foo): Test\ClassModel
    {
        return new Test\ClassModel($foo);
    }

    public function quarter(string $foo): PropertyModel
    {
        return new PropertyModel($foo);
    }

    public function accending(string $foo): CM
    {
        return new CM();
    }
}

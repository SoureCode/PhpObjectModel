# PhpObjectModel

A superset to the [PHP Parser](https://github.com/nikic/PHP-Parser) library that allows you to parse and manipulate PHP
code.
The idea is to create a model to manipulate PHP code like in javascript.

- [Limits](./docs/limits.md)
- [Installation](#installation)
- [Usage](#usage)

## Installation

```bash
composer require sourecode/php-object-model
```

## Usage

```php
<?php

use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
use SoureCode\PhpObjectModel\Type\StringType;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

$classFile = new ClassFile('<?php');
$classFile
    ->setDeclare((new DeclareModel())->setStrictTypes(true))
    ->setNamespace(new NamespaceModel(NamespaceName::fromString('App\\Foo')))
    ->setClass(
        (new ClassModel('Foo'))
            ->addProperty(
                (new PropertyModel('foo'))
                ->setType(new StringType())
                ->setPublic(true)
            )
    );

echo $classFile->getSourceCode();
```

Generates something like this:

```php
<?php

declare(strict_types=1);

namespace App\Foo;

class Foo
{
    public string $foo;
}
```

For more examples see the [tests](./tests).



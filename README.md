
# PhpObjectModel

A superset to the [PHP Parser](https://github.com/nikic/PHP-Parser) library that allows you to parse and manipulate PHP code.
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

$file = new ClassFile(__DIR__.'/Foo.php');

// Get the class
$class = $file->getClass();

// Get the class name
$class->getName();

// Set the class name
$class->setName('Foo');

// Save the file
$file->save();
```

For more examples see the [tests](./tests).



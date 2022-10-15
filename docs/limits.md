
# Limits

## Multiple properties

Multiple properties in a single definition are not supported.

```php
<?php

class Foo
{
    public $foo, $bar;
}
```

## Multiple uses

Multiple uses in a single definition are not supported.

```php
<?php

use Foo, Bar;
```

## Multiple declares

Multiple declare definitions are not supported.

```php
<?php

declare(ticks=4);
declare(strict_types=1);
```

## Multiple attributes

Multiple attributes in a single definition are not supported.

```php
<?php

#[Foo, Bar]
class Foo
{
}
```

Also multiple attributes with the same class are not supported.

```php
<?php

#[Foo]
#[Foo]
class Foo
{
}
```

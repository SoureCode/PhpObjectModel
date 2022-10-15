
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

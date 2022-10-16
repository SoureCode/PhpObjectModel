<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;

class OctalValue extends IntegerValue
{
    public function __construct(Node\Scalar\LNumber|int $node)
    {
        $node = is_int($node) ? new Node\Scalar\LNumber($node, [
            'kind' => Node\Scalar\LNumber::KIND_OCT,
        ]) : $node;

        parent::__construct($node);
    }
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value\Const;

use PhpParser\Node;
use SoureCode\PhpObjectModel\Value\AbstractValue;

/**
 * @extends AbstractValue<Node\Expr\ConstFetch>
 */
class PearInstallDirConstValue extends AbstractValue
{
    public function __construct(Node\Expr\ConstFetch $node = null)
    {
        if (null === $node) {
            $node = new Node\Expr\ConstFetch(new Node\Name('PEAR_INSTALL_DIR'));
        }
        parent::__construct($node);
    }
}

<?php

namespace SoureCode\PhpObjectModel\Tests\Comparer;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\Comparer\AbstractNodeComparer;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class NodeComparerTest extends TestCase
{

    public function testShouldMatch(): void
    {
        $lhs = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    'value'
                ),
                new Node\Expr\Variable('value')
            )
        );

        $rhs = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    'value'
                ),
                new Node\Expr\Variable('value')
            )
        );

        $result = AbstractNodeComparer::compareNodes($lhs, $rhs);

        $this->assertTrue($result);
    }

    public function testShouldAlsoMatch(): void
    {
        $lhs = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    'value'
                ),
                new Node\Expr\New_(
                    new Node\Name(ClassName::class),
                    [
                        new Node\Arg(
                            new Node\Scalar\String_('value')
                        ),
                        new Node\Arg(
                            new Node\Scalar\String_('second')
                        ),
                    ]
                )
            )
        );

        $rhs = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    'value'
                ),
                new Node\Expr\New_(
                    new Node\Name(ClassName::class),
                    [
                        new Node\Arg(
                            new Node\Scalar\String_('value')
                        ),
                        new Node\Arg(
                            new Node\Scalar\String_('second')
                        ),
                    ]
                )
            )
        );

        $result = AbstractNodeComparer::compareNodes($lhs, $rhs);

        $this->assertTrue($result);
    }

}

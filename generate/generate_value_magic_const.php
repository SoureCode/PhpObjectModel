<?php

declare(strict_types=1);

use PhpParser\Comment\Doc;
use PhpParser\Node;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassMethodModel;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\DeclareModel;
use SoureCode\PhpObjectModel\Model\ParameterModel;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\Value\AbstractValue;
use SoureCode\PhpObjectModel\Value\NullValue;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

require_once __DIR__ . '/../vendor/autoload.php';

$classes = [
    'LINE',
    'FILE',
    'DIR',
    'FUNCTION',
    'CLASS',
    'TRAIT',
    'METHOD',
    'NAMESPACE',
];

function isReserved(string $word): bool
{
    return match (strtolower($word)) {
        'namespace' => true,
        'class' => true,
        'trait' => true,
        'function' => true,
        default => false,
    };
}

$directory = __DIR__ . '/../src/Value/MagicConst';
$namespace = ClassName::fromString(NullValue::class)->getNamespace()->namespace('MagicConst');

foreach ($classes as $constName) {
    $isReserved = isReserved($constName);
    $nodeName = ucfirst(strtolower($constName)) . ($isReserved ? '_' : '');
    $class = ucfirst(strtolower($constName)) . 'MagicConstValue';
    $className = $namespace->class($class);
    $fileName = $directory . '/' . $class . '.php';

    $classFile = new ClassFile();
    $classFile->setDeclare((new DeclareModel())->setStrictTypes(true));
    $classFile->addUse('PhpParser\\Node');
    $classFile->setNamespace($className->getNamespace());
    $class = new ClassModel($className);
    $classFile->setClass($class);

    $class->extend(AbstractValue::class);
    $classNode = $class->getNode();

    $constructor = new ClassMethodModel('__construct');
    $constructor->addParameter(
        (new ParameterModel(
            'node',
            new ClassType('PhpParser\\Node\\Scalar\\MagicConst\\' . $nodeName)
        ))
            ->setDefault(new NullValue())
    )
        ->setPublic()
        ->addStatement(
            new Node\Stmt\If_(
                new Node\Expr\BinaryOp\Identical(
                    new Node\Expr\Variable('node'),
                    new Node\Expr\ConstFetch(new Node\Name('null'))
                ),
                [
                    'stmts' => [
                        new Node\Stmt\Expression(
                            new Node\Expr\Assign(
                                new Node\Expr\Variable('node'),
                                new Node\Expr\New_(
                                    new Node\Name(
                                        'Node\\Scalar\\MagicConst\\' . $nodeName,
                                    )
                                )
                            )
                        ),
                    ],
                ]
            )
        )
        ->addStatement(
            new Node\Expr\StaticCall(
                new Node\Name('parent'),
                '__construct',
                [
                    new Node\Arg(new Node\Expr\Variable('node')),
                ]
            )
        );

    $class->addMethod($constructor);

    $lines = [
        '/**',
        ' * @extends AbstractValue<Node\\Scalar\\MagicConst\\' . $nodeName . '>',
        ' */',
    ];

    $classNode->setDocComment(new Doc(implode(PHP_EOL, $lines)));

    echo '[new ' . $className->getShortName() . "(), 'name: __" . $constName . "__']," . PHP_EOL;

    $code = $classFile->getSourceCode();

    file_put_contents($fileName, $code);
}

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
    'PHP_VERSION',
    'PHP_MAJOR_VERSION',
    'PHP_MINOR_VERSION',
    'PHP_RELEASE_VERSION',
    'PHP_VERSION_ID',
    'PHP_EXTRA_VERSION',
    'PHP_ZTS',
    'PHP_DEBUG',
    'PHP_MAXPATHLEN',
    'PHP_OS',
    'PHP_OS_FAMILY',
    'PHP_SAPI',
    'PHP_EOL',
    'PHP_INT_MAX',
    'PHP_INT_MIN',
    'PHP_INT_SIZE',
    'PHP_FLOAT_DIG',
    'PHP_FLOAT_EPSILON',
    'PHP_FLOAT_MIN',
    'PHP_FLOAT_MAX',
    'DEFAULT_INCLUDE_PATH',
    'PEAR_INSTALL_DIR',
    'PEAR_EXTENSION_DIR',
    'PHP_EXTENSION_DIR',
    'PHP_PREFIX',
    'PHP_BINDIR',
    'PHP_BINARY',
    'PHP_MANDIR',
    'PHP_LIBDIR',
    'PHP_DATADIR',
    'PHP_SYSCONFDIR',
    'PHP_LOCALSTATEDIR',
    'PHP_CONFIG_FILE_PATH',
    'PHP_CONFIG_FILE_SCAN_DIR',
    'PHP_SHLIB_SUFFIX',
    'PHP_FD_SETSIZE',
    'E_ERROR',
    'E_WARNING',
    'E_PARSE',
    'E_NOTICE',
    'E_CORE_ERROR',
    'E_CORE_WARNING',
    'E_COMPILE_ERROR',
    'E_COMPILE_WARNING',
    'E_USER_ERROR',
    'E_USER_WARNING',
    'E_USER_NOTICE',
    'E_RECOVERABLE_ERROR',
    'E_DEPRECATED',
    'E_USER_DEPRECATED',
    'E_ALL',
    'E_STRICT',
    '__COMPILER_HALT_OFFSET__',
];

$directory = __DIR__ . '/../src/Value/Const';
$namespace = ClassName::fromString(NullValue::class)->getNamespace()->namespace('Const');

function fixName(string $name): string
{
    return str_replace(
        [
            'PHP_DATADIR',
            'PHP_FD_SETSIZE',
            'PHP_LIBDIR',
            'PHP_LOCALSTATEDIR',
            'PHP_MANDIR',
            'PHP_MAXPATHLEN',
            'PHP_SHLIB_SUFFIX',
            'PHP_SYSCONFDIR',
            'PHP_BINDIR',
        ],
        [
            'PHP_DATA_DIR',
            'PHP_FD_SET_SIZE',
            'PHP_LIB_DIR',
            'PHP_LOCAL_STATE_DIR',
            'PHP_MAN_DIR',
            'PHP_MAX_PATH_LEN',
            'PHP_SH_LIB_SUFFIX',
            'PHP_SYS_CONF_DIR',
            'PHP_BIN_DIR',
        ],
        $name
    );
}

function toTitleCase(string $name): string
{
    $name = fixName($name);

    return str_replace(
        ' ',
        '',
        ucwords(
            strtolower(
                str_replace(
                    '_',
                    ' ',
                    str_replace(
                        '__',
                        '_',
                        $name
                    )
                )
            )
        )
    );
}

foreach ($classes as $constName) {
    $class = toTitleCase($constName) . 'ConstValue';

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
            new ClassType(Node\Expr\ConstFetch::class)
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
                                    new Node\Name('Node\\Expr\\ConstFetch'),
                                    [
                                        new Node\Arg(
                                            new Node\Expr\New_(
                                                new Node\Name('Node\\Name'),
                                                [
                                                    new Node\Arg(
                                                        new Node\Scalar\String_($constName)
                                                    ),
                                                ]
                                            )
                                        ),
                                    ]
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

    $classNode->setDocComment(
        new Doc(
            <<<HEREDOC
/**
 * @extends AbstractValue<Node\Expr\ConstFetch>
 */
HEREDOC
        )
    );

    echo '[new ' . $className->getShortName() . "(), 'name: " . $constName . "']," . PHP_EOL;

    $code = $classFile->getSourceCode();

    file_put_contents($fileName, $code);
}

<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\File;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\ClosureModel;
use SoureCode\PhpObjectModel\Model\DeclareModel;
use SoureCode\PhpObjectModel\Model\NamespaceModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
use SoureCode\PhpObjectModel\ValueObject\NamespaceName;

use function PHPUnit\Framework\assertTrue;

class ClassFileTest extends TestCase
{
    public function testGetSetClass(): void
    {
        $file = new ClassFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleClassA.php'));
        $class = $file->getClass();
        $code = $file->getSourceCode();

        self::assertSame('ExampleClassA', $class->getName());
        self::assertStringContainsString('class ExampleClassA', $code);
        self::assertStringNotContainsString('class Bar', $code);

        $file->setClass(new ClassModel('Bar'));

        $code = $file->getSourceCode();

        self::assertStringContainsString('class Bar', $code);
        self::assertStringNotContainsString('class ExampleClassA', $code);
    }

    public function testHasGetUse(): void
    {
        $file = new ClassFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleClassA.php'));

        assertTrue($file->hasUse(PropertyModel::class));
        assertTrue($file->hasUse(ClosureModel::class));
        assertTrue($file->hasUse('SoureCode\\PhpObjectModel\\Model'));

        self::assertSame('CM', $file->getUseNamespaceName(ClosureModel::class)->getName());
        self::assertSame('Test', $file->getUseNamespaceName('SoureCode\\PhpObjectModel\\Model')->getName());
        self::assertSame('PropertyModel', $file->getUseNamespaceName(PropertyModel::class)->getName());
        self::assertSame('Test\\RandomClass', $file->getUseNamespaceName('SoureCode\\PhpObjectModel\\Model\\RandomClass')->getName());
    }

    public function testAddUseInsertsUseAfterDeclare(): void
    {
        $file = (new ClassFile('<?php'))
            ->setDeclare((new DeclareModel())->setStrictTypes(true));

        $file->addUse(NamespaceName::fromString('SoureCode\\PhpObjectModel\\Model\\ClassModel'));

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(strict_types=1);\nuse SoureCode\\PhpObjectModel\\Model\\ClassModel;", $code);
    }

    public function testAddUserAfterNamespace()
    {
        $file = new ClassFile();

        $file
            ->setDeclare((new DeclareModel())->setStrictTypes(true))
            ->setNamespace('Foo\\Bar')
            ->addUse('Doctrine\\ORM\\Mapping', 'ORM');

        $code = $file->getSourceCode();

        self::assertStringContainsString("namespace Foo\\Bar;\n\nuse Doctrine\\ORM\\Mapping as ORM;", $code);
    }

    public function testSetNamespace(): void
    {
        $file = new ClassFile(
            <<<SOURCECODE
<?php

declare(strict_types=1);

use Acme\ExampleInterface;
use Acme\ExampleAInterface;

class ExampleClass implements ExampleInterface, ExampleAInterface
{
    public function foo(): void
    {
    }
}
SOURCECODE
        );

        $file->setNamespace(new NamespaceModel('Acme\\Foo'));

        $code = $file->getSourceCode();

        self::assertStringContainsString('namespace Acme\\Foo;', $code);
    }
}

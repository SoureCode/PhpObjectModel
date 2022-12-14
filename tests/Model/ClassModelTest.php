<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ArgumentModel;
use SoureCode\PhpObjectModel\Model\AttributeModel;
use SoureCode\PhpObjectModel\Model\ClassMethodModel;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Model\DeclareModel;
use SoureCode\PhpObjectModel\Model\PropertyModel;
use SoureCode\PhpObjectModel\Tests\Fixtures\AbstractBaseClassA;
use SoureCode\PhpObjectModel\Tests\Fixtures\AbstractBaseClassB;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleAInterface;
use SoureCode\PhpObjectModel\Tests\Fixtures\ExampleBInterface;
use SoureCode\PhpObjectModel\Type\ClassType;
use SoureCode\PhpObjectModel\ValueObject\ClassName;

class ClassModelTest extends TestCase
{
    private ?ClassModel $class = null;

    private ?ClassFile $file = null;

    public function setUp(): void
    {
        $this->file = new ClassFile(file_get_contents(__DIR__ . '/../Fixtures/ExampleClassA.php'));
        $this->class = $this->file->getClass();
    }

    public function tearDown(): void
    {
        $this->file = null;
        $this->class = null;
    }

    public function testGetSetName(): void
    {
        $code = $this->file->getSourceCode();

        self::assertSame('ExampleClassA', $this->class->getName()->getShortName());
        self::assertStringContainsString('class ExampleClassA', $code);

        $this->class->setName('Foo');

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('class Foo', $code);
        self::assertStringNotContainsString('class Bar', $code);
    }

    public function testGetSetReadOnly(): void
    {
        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isReadOnly());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('readonly class ExampleClassA', $code);

        $this->class->setReadOnly(true);

        self::assertTrue($this->class->isReadOnly());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString('readonly class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);

        $this->class->setReadOnly(false);

        self::assertFalse($this->class->isReadOnly());

        $code = $this->file->getSourceCode();

        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('readonly class ExampleClassA', $code);
    }

    public function testGetSetFinal(): void
    {
        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isFinal());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('final class ExampleClassA', $code);

        $this->class->setFinal(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($this->class->isFinal());
        self::assertStringContainsString('final class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);

        $this->class->setFinal(false);

        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isFinal());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('final class ExampleClassA', $code);
    }

    public function testGetSetAbstract(): void
    {
        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isAbstract());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('abstract class ExampleClassA', $code);

        $this->class->setAbstract(true);

        $code = $this->file->getSourceCode();

        self::assertTrue($this->class->isAbstract());
        self::assertStringContainsString('abstract class ExampleClassA', $code);
        self::assertStringNotContainsString("\nclass ExampleClassA", $code);

        $this->class->setAbstract(false);

        $code = $this->file->getSourceCode();

        self::assertFalse($this->class->isAbstract());
        self::assertStringContainsString("\nclass ExampleClassA", $code);
        self::assertStringNotContainsString('abstract class ExampleClassA', $code);
    }

    public function testParent(): void
    {
        $code = $this->file->getSourceCode();

        self::assertSame(AbstractBaseClassA::class, $this->class->getExtend());
        self::assertStringContainsString('extends AbstractBaseClassA', $code);

        $this->class->extend(AbstractBaseClassB::class);

        $code = $this->file->getSourceCode();

        self::assertSame(AbstractBaseClassB::class, $this->class->getExtend());
        self::assertStringContainsString('extends AbstractBaseClassB', $code);
        self::assertStringContainsString(sprintf('use %s;', AbstractBaseClassB::class), $code);
    }

    public function testGetSetProperty(): void
    {
        $actual = $this->class->getProperty('staticProperty');
        $code = $this->file->getSourceCode();

        self::assertSame('staticProperty', $actual->getName());
        self::assertTrue($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertFalse($actual->isReadonly());
        self::assertStringContainsString("private static string \$staticProperty = 'foo1';", $code);

        $this->class->addProperty(new PropertyModel('staticProperty7', new ClassType(ClassName::class)));

        $actual = $this->class->getProperty('staticProperty7');
        $code = $this->file->getSourceCode();

        self::assertSame('staticProperty7', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertFalse($actual->isReadonly());
        self::assertStringContainsString('private ClassName $staticProperty7;', $code);
    }

    public function testGetProperties(): void
    {
        $actual = $this->class->getProperties();

        self::assertCount(12, $actual);
    }

    public function testGetAddHasRemoveMethod(): void
    {
        $actual = $this->class->getMethod('baz');

        self::assertSame('baz', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertTrue($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertFalse($actual->isPrivate());
        self::assertFalse($actual->isAbstract());

        $actual = new ClassMethodModel('bazer');
        $actual->setPrivate();
        $actual->addStatement(new Node\Stmt\Return_(new Node\Scalar\String_('foo')));
        $actual->setReturnType(new ClassType(ClassType::class));

        $this->class->addMethod($actual);

        $code = $this->file->getSourceCode();

        self::assertSame('bazer', $actual->getName());
        self::assertFalse($actual->isStatic());
        self::assertFalse($actual->isPublic());
        self::assertFalse($actual->isProtected());
        self::assertTrue($actual->isPrivate());
        self::assertFalse($actual->isAbstract());
        self::assertStringNotContainsString('public function bazer(string $foo, int $bar): string', $code);
        self::assertStringContainsString('private function bazer(): ClassType', $code);
        self::assertStringContainsString(sprintf('use %s;', ClassType::class), $code);
        self::assertStringContainsString('return \'foo\';', $code);
        self::assertTrue($this->class->hasMethod('bazer'));

        $this->class->removeMethod('bazer');

        self::assertFalse($this->class->hasMethod('bazer'));
    }

    public function testImplementsRemoveImplementInterface(): void
    {
        $interfaces = $this->class->getInterfaces();
        $code = $this->file->getSourceCode();

        self::assertCount(1, $interfaces);
        self::assertEquals([ExampleAInterface::class], $interfaces);
        self::assertTrue($this->class->implements(ExampleAInterface::class));
        self::assertFalse($this->class->implements(ExampleBInterface::class));
        self::assertStringContainsString('implements ExampleAInterface' . PHP_EOL, $code);

        $this->class->implement(ExampleBInterface::class);

        $interfaces = $this->class->getInterfaces();
        $code = $this->file->getSourceCode();

        self::assertCount(2, $interfaces);
        self::assertEquals([ExampleAInterface::class, ExampleBInterface::class], $interfaces);
        self::assertTrue($this->class->implements(ExampleAInterface::class));
        self::assertTrue($this->class->implements(ExampleBInterface::class));
        self::assertStringContainsString('implements ExampleAInterface, ExampleBInterface' . PHP_EOL, $code);
        self::assertStringContainsString(sprintf('use %s;', ExampleBInterface::class), $code);

        $this->class->removeInterface(ExampleAInterface::class);

        $interfaces = $this->class->getInterfaces();
        $code = $this->file->getSourceCode();

        self::assertCount(1, $interfaces);
        self::assertEquals([ExampleBInterface::class], $interfaces);
        self::assertTrue($this->class->implements(ExampleBInterface::class));
        self::assertFalse($this->class->implements(ExampleAInterface::class));
        self::assertStringContainsString('implements ExampleBInterface' . PHP_EOL, $code);
        self::assertStringContainsString(sprintf('use %s;', ExampleBInterface::class), $code);
    }

    public function testGetSetHasDeclare(): void
    {
        $file = new ClassFile('<?php');

        self::assertFalse($file->hasDeclare());

        $model = new DeclareModel();

        $file->setDeclare($model);

        self::assertTrue($file->hasDeclare());

        $declare = $file->getDeclare();

        self::assertSame($model->getNode(), $declare->getNode());
    }

    public function testGetSetHasAttribute(): void
    {
        $file = (new ClassFile(
            <<<HEREDOC

<?php

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Foo
{
}

HEREDOC
        ));

        $class = $file->getClass();

        self::assertTrue($class->hasAttribute('Doctrine\\ORM\\Mapping\\Entity'));
        self::assertFalse($class->hasAttribute('Doctrine\\ORM\\Mapping\\Table'));

        $class->addAttribute(
            (new AttributeModel('Doctrine\\ORM\\Mapping\\Table'))
                ->setArguments([
                    new ArgumentModel(new Node\Arg(new Node\Scalar\String_('foo'))),
                ])
        );

        $class->getAttribute('Doctrine\\ORM\\Mapping\\Entity')
            ->setArgument(new Node\Arg(new Node\Scalar\String_('bar')));

        self::assertTrue($class->hasAttribute('Doctrine\\ORM\\Mapping\\Table'));
        self::assertTrue($class->hasAttribute('Doctrine\\ORM\\Mapping\\Entity'));

        $code = $file->getSourceCode();

        self::assertStringContainsString('use Doctrine\\ORM\\Mapping as ORM;', $code);
        self::assertStringContainsString("#[ORM\\Entity('bar')]", $code);
        self::assertStringContainsString("#[ORM\\Table('foo')]", $code);

        $class->removeAttribute('Doctrine\\ORM\\Mapping\\Entity');

        $code = $file->getSourceCode();

        self::assertStringNotContainsString("#[ORM\\Entity('bar')]", $code);
    }
}

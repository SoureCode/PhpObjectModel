<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Model;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\DeclareModel;

class DeclareModelTest extends TestCase
{
    public function testSetStrictTypesAddStrictTypesWhenSettingStrictTypes(): void
    {
        $file = new ClassFile('<?php');
        $model = new DeclareModel();
        $file->setDeclare($model);

        $model->setStrictTypes(true);

        $code = $file->getSourceCode();

        self::assertStringContainsString('declare(strict_types=1);', $code);
    }

    public function testSetStrictTypesRemoveStrictTypesWhenUnsettingStrictTypes(): void
    {
        $file = new ClassFile('<?php declare(strict_types=1);');
        $model = $file->getDeclare();

        $model->setStrictTypes(false);

        $code = $file->getSourceCode();

        self::assertStringNotContainsString('declare', $code);
    }

    public function testSetStrictTypesChangeStrictTypesWhenSettingStrictTypes(): void
    {
        $file = new ClassFile('<?php declare(strict_types=0);');
        $model = $file->getDeclare();

        $model->setStrictTypes(true);

        $code = $file->getSourceCode();

        self::assertStringContainsString('declare(strict_types=1);', $code);
    }

    public function testSetTicksAddTicksWhenSettingTicks(): void
    {
        $file = new ClassFile('<?php');
        $model = new DeclareModel();
        $file->setDeclare($model);

        $model->setTicks(1);

        $code = $file->getSourceCode();

        self::assertStringContainsString('declare(ticks=1);', $code);
    }

    public function testSetTicksRemoveTicksWhenUnsettingTicks(): void
    {
        $file = new ClassFile('<?php declare(ticks=1);');
        $model = $file->getDeclare();

        $model->setTicks(null);

        $code = $file->getSourceCode();

        self::assertStringNotContainsString('declare', $code);
    }

    public function testSetTicksChangeTicksWhenSettingTicks(): void
    {
        $file = new ClassFile('<?php declare(ticks=0);');
        $model = $file->getDeclare();

        $model->setTicks(1);

        $code = $file->getSourceCode();

        self::assertStringContainsString('declare(ticks=1);', $code);
    }

    public function testSetStrictTypesAndTicksAddStrictTypesAndTicksWhenSettingStrictTypesAndTicks(): void
    {
        $file = new ClassFile('<?php');
        $model = new DeclareModel();
        $file->setDeclare($model);

        $model->setStrictTypes(true);
        $model->setTicks(1);

        $code = $file->getSourceCode();

        self::assertStringContainsString('declare(strict_types=1, ticks=1);', $code);
    }

    public function testSetStrictTypesAndTicksRemoveStrictTypesAndTicksWhenUnsettingStrictTypesAndTicks(): void
    {
        $file = new ClassFile('<?php declare(strict_types=1, ticks=1);');
        $model = $file->getDeclare();

        $model->setStrictTypes(false);
        $model->setTicks(null);

        $code = $file->getSourceCode();

        self::assertStringNotContainsString('declare', $code);
    }

    public function testSetStrictTypesAndTicksChangeStrictTypesAndTicksWhenSettingStrictTypesAndTicks(): void
    {
        $file = new ClassFile('<?php declare(strict_types=0, ticks=0);');
        $model = $file->getDeclare();

        $model->setStrictTypes(true);
        $model->setTicks(1);

        $code = $file->getSourceCode();

        self::assertStringContainsString('declare(strict_types=1, ticks=1);', $code);
    }

    public function testSetEncodingAddEncodingWhenSettingEncoding(): void
    {
        $file = new ClassFile('<?php');
        $model = new DeclareModel();
        $file->setDeclare($model);

        $model->setEncoding('UTF-8');

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(encoding='UTF-8');", $code);
    }

    public function testSetEncodingRemoveEncodingWhenUnsettingEncoding(): void
    {
        $file = new ClassFile('<?php declare(encoding="UTF-8");');
        $model = $file->getDeclare();

        $model->setEncoding(null);

        $code = $file->getSourceCode();

        self::assertStringNotContainsString('declare', $code);
    }

    public function testSetEncodingChangeEncodingWhenSettingEncoding(): void
    {
        $file = new ClassFile('<?php declare(encoding="ISO-8859-1");');
        $model = $file->getDeclare();

        $model->setEncoding('UTF-8');

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(encoding='UTF-8');", $code);
    }

    public function testSetStrictTypesAndEncodingAddStrictTypesAndEncodingWhenSettingStrictTypesAndEncoding(): void
    {
        $file = new ClassFile('<?php');
        $model = new DeclareModel();
        $file->setDeclare($model);

        $model->setStrictTypes(true);
        $model->setEncoding('UTF-8');

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(strict_types=1, encoding='UTF-8');", $code);
    }

    public function testSetStrictTypesAndEncodingRemoveStrictTypesAndEncodingWhenUnsettingStrictTypesAndEncoding(): void
    {
        $file = new ClassFile('<?php declare(strict_types=1, encoding="UTF-8");');
        $model = $file->getDeclare();

        $model->setStrictTypes(false);
        $model->setEncoding(null);

        $code = $file->getSourceCode();

        self::assertStringNotContainsString('declare', $code);
    }

    public function testSetStrictTypesAndEncodingChangeStrictTypesAndEncodingWhenSettingStrictTypesAndEncoding(): void
    {
        $file = new ClassFile('<?php declare(strict_types=0, encoding="ISO-8859-1");');
        $model = $file->getDeclare();

        $model->setStrictTypes(true);
        $model->setEncoding('UTF-8');

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(strict_types=1, encoding='UTF-8');", $code);
    }

    public function testSetTicksAndEncodingAddTicksAndEncodingWhenSettingTicksAndEncoding(): void
    {
        $file = new ClassFile('<?php');
        $model = new DeclareModel();
        $file->setDeclare($model);

        $model->setTicks(1);
        $model->setEncoding('UTF-8');

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(ticks=1, encoding='UTF-8');", $code);
    }

    public function testSetTicksAndEncodingRemoveTicksAndEncodingWhenUnsettingTicksAndEncoding(): void
    {
        $file = new ClassFile('<?php declare(ticks=1, encoding="UTF-8");');
        $model = $file->getDeclare();

        $model->setTicks(null);
        $model->setEncoding(null);

        $code = $file->getSourceCode();

        self::assertStringNotContainsString('declare', $code);
    }

    public function testSetTicksAndEncodingChangeTicksAndEncodingWhenSettingTicksAndEncoding(): void
    {
        $file = new ClassFile('<?php declare(ticks=0, encoding="ISO-8859-1");');
        $model = $file->getDeclare();

        $model->setTicks(1);
        $model->setEncoding('UTF-8');

        $code = $file->getSourceCode();

        self::assertStringContainsString("declare(ticks=1, encoding='UTF-8');", $code);
    }
}

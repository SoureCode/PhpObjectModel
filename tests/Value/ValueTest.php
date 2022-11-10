<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Tests\Value;

use PHPUnit\Framework\TestCase;
use SoureCode\PhpObjectModel\File\ClassFile;
use SoureCode\PhpObjectModel\Model\ArgumentModel;
use SoureCode\PhpObjectModel\Model\AttributeModel;
use SoureCode\PhpObjectModel\Model\ClassModel;
use SoureCode\PhpObjectModel\Value\BinaryValue;
use SoureCode\PhpObjectModel\Value\BooleanValue;
use SoureCode\PhpObjectModel\Value\ClassConstValue;
use SoureCode\PhpObjectModel\Value\Const\CompilerHaltOffsetConstValue;
use SoureCode\PhpObjectModel\Value\Const\DefaultIncludePathConstValue;
use SoureCode\PhpObjectModel\Value\Const\EAllConstValue;
use SoureCode\PhpObjectModel\Value\Const\ECompileErrorConstValue;
use SoureCode\PhpObjectModel\Value\Const\ECompileWarningConstValue;
use SoureCode\PhpObjectModel\Value\Const\ECoreErrorConstValue;
use SoureCode\PhpObjectModel\Value\Const\ECoreWarningConstValue;
use SoureCode\PhpObjectModel\Value\Const\EDeprecatedConstValue;
use SoureCode\PhpObjectModel\Value\Const\EErrorConstValue;
use SoureCode\PhpObjectModel\Value\Const\ENoticeConstValue;
use SoureCode\PhpObjectModel\Value\Const\EParseConstValue;
use SoureCode\PhpObjectModel\Value\Const\ERecoverableErrorConstValue;
use SoureCode\PhpObjectModel\Value\Const\EStrictConstValue;
use SoureCode\PhpObjectModel\Value\Const\EUserDeprecatedConstValue;
use SoureCode\PhpObjectModel\Value\Const\EUserErrorConstValue;
use SoureCode\PhpObjectModel\Value\Const\EUserNoticeConstValue;
use SoureCode\PhpObjectModel\Value\Const\EUserWarningConstValue;
use SoureCode\PhpObjectModel\Value\Const\EWarningConstValue;
use SoureCode\PhpObjectModel\Value\Const\PearExtensionDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PearInstallDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpBinaryConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpBinDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpConfigFilePathConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpConfigFileScanDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpDataDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpDebugConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpEolConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpExtensionDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpExtraVersionConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpFdSetSizeConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpFloatDigConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpFloatEpsilonConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpFloatMaxConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpFloatMinConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpIntMaxConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpIntMinConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpIntSizeConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpLibDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpLocalStateDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpMajorVersionConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpManDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpMaxPathLenConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpMinorVersionConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpOsConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpOsFamilyConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpPrefixConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpReleaseVersionConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpSapiConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpShLibSuffixConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpSysConfDirConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpVersionConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpVersionIdConstValue;
use SoureCode\PhpObjectModel\Value\Const\PhpZtsConstValue;
use SoureCode\PhpObjectModel\Value\FloatValue;
use SoureCode\PhpObjectModel\Value\HexadecimalValue;
use SoureCode\PhpObjectModel\Value\IntegerValue;
use SoureCode\PhpObjectModel\Value\MagicConst\ClassMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\DirMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\FileMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\FunctionMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\LineMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\MethodMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\NamespaceMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\TraitMagicConstValue;
use SoureCode\PhpObjectModel\Value\NullValue;
use SoureCode\PhpObjectModel\Value\OctalValue;
use SoureCode\PhpObjectModel\Value\StringValue;
use SoureCode\PhpObjectModel\Value\ValueInterface;
use SoureCode\PhpObjectModel\Value\VariableValue;

class ValueTest extends TestCase
{
    public function provideValues(): array
    {
        return [
            [new BinaryValue(24), 'name: 0b11000'],
            [new BooleanValue(true), 'name: true'],
            [new BooleanValue(false), 'name: false'],
            [new ClassConstValue(StringValue::class), 'name: StringValue::class'],
            [new FloatValue(1.2), 'name: 1.2'],
            [new HexadecimalValue(24), 'name: 0x18'],
            [new IntegerValue(42), 'name: 42'],
            [new NullValue(), 'name: null'],
            [new OctalValue(24), 'name: 030'],
            [new StringValue('foo'), 'name: \'foo\''],
            [new VariableValue('foo'), 'name: $foo'],

            // magic constants
            [new LineMagicConstValue(), 'name: __LINE__'],
            [new FileMagicConstValue(), 'name: __FILE__'],
            [new DirMagicConstValue(), 'name: __DIR__'],
            [new FunctionMagicConstValue(), 'name: __FUNCTION__'],
            [new ClassMagicConstValue(), 'name: __CLASS__'],
            [new TraitMagicConstValue(), 'name: __TRAIT__'],
            [new MethodMagicConstValue(), 'name: __METHOD__'],
            [new NamespaceMagicConstValue(), 'name: __NAMESPACE__'],

            // predefined constants
            [new PhpVersionConstValue(), 'name: PHP_VERSION'],
            [new PhpMajorVersionConstValue(), 'name: PHP_MAJOR_VERSION'],
            [new PhpMinorVersionConstValue(), 'name: PHP_MINOR_VERSION'],
            [new PhpReleaseVersionConstValue(), 'name: PHP_RELEASE_VERSION'],
            [new PhpVersionIdConstValue(), 'name: PHP_VERSION_ID'],
            [new PhpExtraVersionConstValue(), 'name: PHP_EXTRA_VERSION'],
            [new PhpZtsConstValue(), 'name: PHP_ZTS'],
            [new PhpDebugConstValue(), 'name: PHP_DEBUG'],
            [new PhpMaxPathLenConstValue(), 'name: PHP_MAXPATHLEN'],
            [new PhpOsConstValue(), 'name: PHP_OS'],
            [new PhpOsFamilyConstValue(), 'name: PHP_OS_FAMILY'],
            [new PhpSapiConstValue(), 'name: PHP_SAPI'],
            [new PhpEolConstValue(), 'name: PHP_EOL'],
            [new PhpIntMaxConstValue(), 'name: PHP_INT_MAX'],
            [new PhpIntMinConstValue(), 'name: PHP_INT_MIN'],
            [new PhpIntSizeConstValue(), 'name: PHP_INT_SIZE'],
            [new PhpFloatDigConstValue(), 'name: PHP_FLOAT_DIG'],
            [new PhpFloatEpsilonConstValue(), 'name: PHP_FLOAT_EPSILON'],
            [new PhpFloatMinConstValue(), 'name: PHP_FLOAT_MIN'],
            [new PhpFloatMaxConstValue(), 'name: PHP_FLOAT_MAX'],
            [new DefaultIncludePathConstValue(), 'name: DEFAULT_INCLUDE_PATH'],
            [new PearInstallDirConstValue(), 'name: PEAR_INSTALL_DIR'],
            [new PearExtensionDirConstValue(), 'name: PEAR_EXTENSION_DIR'],
            [new PhpExtensionDirConstValue(), 'name: PHP_EXTENSION_DIR'],
            [new PhpPrefixConstValue(), 'name: PHP_PREFIX'],
            [new PhpBinDirConstValue(), 'name: PHP_BINDIR'],
            [new PhpBinaryConstValue(), 'name: PHP_BINARY'],
            [new PhpManDirConstValue(), 'name: PHP_MANDIR'],
            [new PhpLibDirConstValue(), 'name: PHP_LIBDIR'],
            [new PhpDataDirConstValue(), 'name: PHP_DATADIR'],
            [new PhpSysConfDirConstValue(), 'name: PHP_SYSCONFDIR'],
            [new PhpLocalStateDirConstValue(), 'name: PHP_LOCALSTATEDIR'],
            [new PhpConfigFilePathConstValue(), 'name: PHP_CONFIG_FILE_PATH'],
            [new PhpConfigFileScanDirConstValue(), 'name: PHP_CONFIG_FILE_SCAN_DIR'],
            [new PhpShLibSuffixConstValue(), 'name: PHP_SHLIB_SUFFIX'],
            [new PhpFdSetSizeConstValue(), 'name: PHP_FD_SETSIZE'],
            [new EErrorConstValue(), 'name: E_ERROR'],
            [new EWarningConstValue(), 'name: E_WARNING'],
            [new EParseConstValue(), 'name: E_PARSE'],
            [new ENoticeConstValue(), 'name: E_NOTICE'],
            [new ECoreErrorConstValue(), 'name: E_CORE_ERROR'],
            [new ECoreWarningConstValue(), 'name: E_CORE_WARNING'],
            [new ECompileErrorConstValue(), 'name: E_COMPILE_ERROR'],
            [new ECompileWarningConstValue(), 'name: E_COMPILE_WARNING'],
            [new EUserErrorConstValue(), 'name: E_USER_ERROR'],
            [new EUserWarningConstValue(), 'name: E_USER_WARNING'],
            [new EUserNoticeConstValue(), 'name: E_USER_NOTICE'],
            [new ERecoverableErrorConstValue(), 'name: E_RECOVERABLE_ERROR'],
            [new EDeprecatedConstValue(), 'name: E_DEPRECATED'],
            [new EUserDeprecatedConstValue(), 'name: E_USER_DEPRECATED'],
            [new EAllConstValue(), 'name: E_ALL'],
            [new EStrictConstValue(), 'name: E_STRICT'],
            [new CompilerHaltOffsetConstValue(), 'name: __COMPILER_HALT_OFFSET__'],
        ];
    }

    /**
     * @dataProvider provideValues
     */
    public function testSetArgumentShouldSetValue(ValueInterface $value, string $expected): void
    {
        $classFile = new ClassFile();
        $classModel = new ClassModel('Foo');
        $classFile->setClass($classModel);
        $attribute = new AttributeModel('Bar');
        $classModel->addAttribute($attribute);

        $argument = new ArgumentModel('name', $value);
        $attribute->setArgument($argument);

        $code = $classFile->getSourceCode();

        self::assertStringContainsString($expected, $code);
    }
}

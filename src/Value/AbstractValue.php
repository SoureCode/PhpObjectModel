<?php

declare(strict_types=1);

namespace SoureCode\PhpObjectModel\Value;

use PhpParser\Node;
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
use SoureCode\PhpObjectModel\Value\MagicConst\ClassMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\DirMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\FileMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\FunctionMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\LineMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\MethodMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\NamespaceMagicConstValue;
use SoureCode\PhpObjectModel\Value\MagicConst\TraitMagicConstValue;

/**
 * @template T of Node\Expr
 */
abstract class AbstractValue
{
    /**
     * @psalm-var T
     */
    protected Node\Expr $node;

    /**
     * @psalm-param T $node
     */
    public function __construct(Node\Expr $node)
    {
        $this->node = $node;
    }

    /**
     * @psalm-return T
     */
    public function getNode(): Node\Expr
    {
        return $this->node;
    }

    public static function fromNode(Node $node): ?AbstractValue
    {
        if ($node instanceof Node\Scalar\String_) {
            return new StringValue($node);
        }

        if ($node instanceof Node\Scalar\LNumber) {
            $kind = Node\Scalar\LNumber::KIND_DEC;

            if ($node->hasAttribute('kind')) {
                $kind = (int) $node->getAttribute('kind');
            }

            return match ($kind) {
                Node\Scalar\LNumber::KIND_DEC => new IntegerValue($node),
                Node\Scalar\LNumber::KIND_BIN => new BinaryValue($node),
                Node\Scalar\LNumber::KIND_OCT => new OctalValue($node),
                Node\Scalar\LNumber::KIND_HEX => new HexadecimalValue($node),
            };
        }

        if ($node instanceof Node\Scalar\DNumber) {
            return new FloatValue($node);
        }

        if ($node instanceof Node\Expr\Variable) {
            return new VariableValue($node);
        }

        if ($node instanceof Node\Expr\ConstFetch) {
            $type = $node->name->getLast();

            return match ($type) {
                'true' => new BooleanValue($node),
                'false' => new BooleanValue($node),
                'null' => new NullValue($node),
                'PHP_VERSION' => new PhpVersionConstValue($node),
                'PHP_MAJOR_VERSION' => new PhpMajorVersionConstValue($node),
                'PHP_MINOR_VERSION' => new PhpMinorVersionConstValue($node),
                'PHP_RELEASE_VERSION' => new PhpReleaseVersionConstValue($node),
                'PHP_VERSION_ID' => new PhpVersionIdConstValue($node),
                'PHP_EXTRA_VERSION' => new PhpExtraVersionConstValue($node),
                'PHP_ZTS' => new PhpZtsConstValue($node),
                'PHP_DEBUG' => new PhpDebugConstValue($node),
                'PHP_MAXPATHLEN' => new PhpMaxPathLenConstValue($node),
                'PHP_OS' => new PhpOsConstValue($node),
                'PHP_OS_FAMILY' => new PhpOsFamilyConstValue($node),
                'PHP_SAPI' => new PhpSapiConstValue($node),
                'PHP_EOL' => new PhpEolConstValue($node),
                'PHP_INT_MAX' => new PhpIntMaxConstValue($node),
                'PHP_INT_MIN' => new PhpIntMinConstValue($node),
                'PHP_INT_SIZE' => new PhpIntSizeConstValue($node),
                'PHP_FLOAT_DIG' => new PhpFloatDigConstValue($node),
                'PHP_FLOAT_EPSILON' => new PhpFloatEpsilonConstValue($node),
                'PHP_FLOAT_MIN' => new PhpFloatMinConstValue($node),
                'PHP_FLOAT_MAX' => new PhpFloatMaxConstValue($node),
                'DEFAULT_INCLUDE_PATH' => new DefaultIncludePathConstValue($node),
                'PEAR_INSTALL_DIR' => new PearInstallDirConstValue($node),
                'PEAR_EXTENSION_DIR' => new PearExtensionDirConstValue($node),
                'PHP_EXTENSION_DIR' => new PhpExtensionDirConstValue($node),
                'PHP_PREFIX' => new PhpPrefixConstValue($node),
                'PHP_BINDIR' => new PhpBinDirConstValue($node),
                'PHP_BINARY' => new PhpBinaryConstValue($node),
                'PHP_MANDIR' => new PhpManDirConstValue($node),
                'PHP_LIBDIR' => new PhpLibDirConstValue($node),
                'PHP_DATADIR' => new PhpDataDirConstValue($node),
                'PHP_SYSCONFDIR' => new PhpSysConfDirConstValue($node),
                'PHP_LOCALSTATEDIR' => new PhpLocalStateDirConstValue($node),
                'PHP_CONFIG_FILE_PATH' => new PhpConfigFilePathConstValue($node),
                'PHP_CONFIG_FILE_SCAN_DIR' => new PhpConfigFileScanDirConstValue($node),
                'PHP_SHLIB_SUFFIX' => new PhpShLibSuffixConstValue($node),
                'PHP_FD_SETSIZE' => new PhpFdSetSizeConstValue($node),
                'E_ERROR' => new EErrorConstValue($node),
                'E_WARNING' => new EWarningConstValue($node),
                'E_PARSE' => new EParseConstValue($node),
                'E_NOTICE' => new ENoticeConstValue($node),
                'E_CORE_ERROR' => new ECoreErrorConstValue($node),
                'E_CORE_WARNING' => new ECoreWarningConstValue($node),
                'E_COMPILE_ERROR' => new ECompileErrorConstValue($node),
                'E_COMPILE_WARNING' => new ECompileWarningConstValue($node),
                'E_USER_ERROR' => new EUserErrorConstValue($node),
                'E_USER_WARNING' => new EUserWarningConstValue($node),
                'E_USER_NOTICE' => new EUserNoticeConstValue($node),
                'E_RECOVERABLE_ERROR' => new ERecoverableErrorConstValue($node),
                'E_DEPRECATED' => new EDeprecatedConstValue($node),
                'E_USER_DEPRECATED' => new EUserDeprecatedConstValue($node),
                'E_ALL' => new EAllConstValue($node),
                'E_STRICT' => new EStrictConstValue($node),
                '__COMPILER_HALT_OFFSET__' => new CompilerHaltOffsetConstValue($node),
                default => null,
            };
        }

        if ($node instanceof Node\Scalar\MagicConst) {
            return match (true) {
                $node instanceof Node\Scalar\MagicConst\Line => new LineMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\File => new FileMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\Dir => new DirMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\Namespace_ => new NamespaceMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\Function_ => new FunctionMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\Class_ => new ClassMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\Method => new MethodMagicConstValue($node),
                $node instanceof Node\Scalar\MagicConst\Trait_ => new TraitMagicConstValue($node),
                default => null,
            };
        }

        if ($node instanceof Node\Expr\ClassConstFetch) {
            return new ClassConstValue($node);
        }

        return null;
    }
}

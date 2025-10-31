<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\EncryptionException;

/**
 * @internal
 */
#[CoversClass(EncryptionException::class)]
final class EncryptionExceptionTest extends AbstractExceptionTestCase
{
}

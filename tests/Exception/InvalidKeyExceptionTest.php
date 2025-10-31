<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\InvalidKeyException;

/**
 * @internal
 */
#[CoversClass(InvalidKeyException::class)]
final class InvalidKeyExceptionTest extends AbstractExceptionTestCase
{
}

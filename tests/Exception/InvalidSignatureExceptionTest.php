<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\InvalidSignatureException;

/**
 * @internal
 */
#[CoversClass(InvalidSignatureException::class)]
final class InvalidSignatureExceptionTest extends AbstractExceptionTestCase
{
}

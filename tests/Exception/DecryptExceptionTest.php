<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\DecryptException;

/**
 * @internal
 */
#[CoversClass(DecryptException::class)]
final class DecryptExceptionTest extends AbstractExceptionTestCase
{
}

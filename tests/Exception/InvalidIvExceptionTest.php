<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\InvalidIvException;

/**
 * @internal
 */
#[CoversClass(InvalidIvException::class)]
final class InvalidIvExceptionTest extends AbstractExceptionTestCase
{
}

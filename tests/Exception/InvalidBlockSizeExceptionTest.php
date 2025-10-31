<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\InvalidBlockSizeException;

/**
 * @internal
 */
#[CoversClass(InvalidBlockSizeException::class)]
final class InvalidBlockSizeExceptionTest extends AbstractExceptionTestCase
{
}

<?php

namespace Tourze\WechatHelper\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatHelper\Exception\InvalidAppIdException;

/**
 * @internal
 */
#[CoversClass(InvalidAppIdException::class)]
final class InvalidAppIdExceptionTest extends AbstractExceptionTestCase
{
}

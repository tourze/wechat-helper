<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\Exception\InvalidIvException;

class InvalidIvExceptionTest extends TestCase
{
    public function testExceptionExtendsInvalidArgumentException()
    {
        $exception = new InvalidIvException();
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test invalid iv exception message';
        $exception = new InvalidIvException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test invalid iv exception message';
        $code = 123;
        $exception = new InvalidIvException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
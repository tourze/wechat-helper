<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\WechatHelper\Exception\InvalidAppIdException;

class InvalidAppIdExceptionTest extends TestCase
{
    public function testExceptionExtendsRuntimeException()
    {
        $exception = new InvalidAppIdException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test invalid app id exception message';
        $exception = new InvalidAppIdException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test invalid app id exception message';
        $code = 123;
        $exception = new InvalidAppIdException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
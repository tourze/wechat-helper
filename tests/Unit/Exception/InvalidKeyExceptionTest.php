<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\Exception\InvalidKeyException;

class InvalidKeyExceptionTest extends TestCase
{
    public function testExceptionExtendsInvalidArgumentException()
    {
        $exception = new InvalidKeyException();
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test invalid key exception message';
        $exception = new InvalidKeyException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test invalid key exception message';
        $code = 123;
        $exception = new InvalidKeyException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
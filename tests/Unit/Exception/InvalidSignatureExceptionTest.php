<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\WechatHelper\Exception\InvalidSignatureException;

class InvalidSignatureExceptionTest extends TestCase
{
    public function testExceptionExtendsRuntimeException()
    {
        $exception = new InvalidSignatureException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test invalid signature exception message';
        $exception = new InvalidSignatureException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test invalid signature exception message';
        $code = 123;
        $exception = new InvalidSignatureException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
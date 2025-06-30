<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\WechatHelper\Exception\InvalidBlockSizeException;

class InvalidBlockSizeExceptionTest extends TestCase
{
    public function testExceptionExtendsRuntimeException()
    {
        $exception = new InvalidBlockSizeException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test invalid block size exception message';
        $exception = new InvalidBlockSizeException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test invalid block size exception message';
        $code = 123;
        $exception = new InvalidBlockSizeException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
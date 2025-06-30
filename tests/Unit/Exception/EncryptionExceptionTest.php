<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\WechatHelper\Exception\EncryptionException;

class EncryptionExceptionTest extends TestCase
{
    public function testExceptionExtendsRuntimeException()
    {
        $exception = new EncryptionException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test encryption exception message';
        $exception = new EncryptionException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test encryption exception message';
        $code = 123;
        $exception = new EncryptionException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
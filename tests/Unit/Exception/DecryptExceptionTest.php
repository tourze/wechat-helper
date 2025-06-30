<?php

namespace Tourze\WechatHelper\Tests\Unit\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\Exception\DecryptException;

class DecryptExceptionTest extends TestCase
{
    public function testExceptionExtendsBaseException()
    {
        $exception = new DecryptException();
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test decrypt exception message';
        $exception = new DecryptException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test decrypt exception message';
        $code = 123;
        $exception = new DecryptException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPreviousException()
    {
        $previousException = new Exception('Previous exception');
        $exception = new DecryptException('Test message', 0, $previousException);
        
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
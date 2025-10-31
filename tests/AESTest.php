<?php

namespace Tourze\WechatHelper\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\AES;
use Tourze\WechatHelper\Exception\InvalidIvException;
use Tourze\WechatHelper\Exception\InvalidKeyException;

/**
 * @internal
 */
#[CoversClass(AES::class)]
final class AESTest extends TestCase
{
    /**
     * 测试使用16字节密钥的加密功能
     */
    public function testEncryptWithValidKey16Bytes(): void
    {
        $text = 'Hello World';
        $key = str_repeat('a', 16);
        $iv = str_repeat('b', 16);

        $encrypted = AES::encrypt($text, $key, $iv);
        $decrypted = AES::decrypt($encrypted, $key, $iv);

        $this->assertEquals($text, $decrypted);
    }

    /**
     * 测试使用24字节密钥的加密功能
     */
    public function testEncryptWithValidKey24Bytes(): void
    {
        $text = 'Hello World';
        $key = str_repeat('a', 24);
        $iv = str_repeat('b', 16);

        $encrypted = AES::encrypt($text, $key, $iv);
        $decrypted = AES::decrypt($encrypted, $key, $iv);

        $this->assertEquals($text, $decrypted);
    }

    /**
     * 测试使用32字节密钥的加密功能
     */
    public function testEncryptWithValidKey32Bytes(): void
    {
        $text = 'Hello World';
        $key = str_repeat('a', 32);
        $iv = str_repeat('b', 16);

        $encrypted = AES::encrypt($text, $key, $iv);
        $decrypted = AES::decrypt($encrypted, $key, $iv);

        $this->assertEquals($text, $decrypted);
    }

    /**
     * 测试使用无效长度密钥时抛出的异常
     */
    public function testEncryptWithInvalidKeyLength(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('Key length must be 16, 24, or 32 bytes');

        $text = 'Hello World';
        $key = str_repeat('a', 20); // 无效长度的密钥
        $iv = str_repeat('b', 16);

        AES::encrypt($text, $key, $iv);
    }

    /**
     * 测试使用自定义解密方法
     */
    public function testDecryptWithCustomMethod(): void
    {
        $text = 'Hello World';
        $key = str_repeat('a', 16);
        $iv = str_repeat('b', 16);
        $method = 'aes-128-cbc';

        $encrypted = openssl_encrypt($text, $method, $key, OPENSSL_RAW_DATA, $iv);
        $this->assertIsString($encrypted);
        $decrypted = AES::decrypt($encrypted, $key, $iv, OPENSSL_RAW_DATA, $method);

        $this->assertEquals($text, $decrypted);
    }

    /**
     * 测试使用无效IV长度时抛出的异常
     */
    public function testDecryptWithInvalidIvLength(): void
    {
        $this->expectException(InvalidIvException::class);
        $this->expectExceptionMessage('IV length must be 16 bytes');

        $encrypted = 'dummy-encrypted-text';
        $key = str_repeat('a', 16);
        $iv = str_repeat('b', 15); // 无效长度的IV

        AES::decrypt($encrypted, $key, $iv);
    }

    /**
     * 测试getMode方法返回正确的加密模式
     */
    public function testGetModeReturnsCorrectMode(): void
    {
        $key16 = str_repeat('a', 16);
        $key24 = str_repeat('a', 24);
        $key32 = str_repeat('a', 32);

        $this->assertEquals('aes-128-cbc', AES::getMode($key16));
        $this->assertEquals('aes-192-cbc', AES::getMode($key24));
        $this->assertEquals('aes-256-cbc', AES::getMode($key32));
    }

    /**
     * 测试有效密钥验证
     */
    public function testValidateKeyWithValidKey(): void
    {
        $key16 = str_repeat('a', 16);
        $key24 = str_repeat('a', 24);
        $key32 = str_repeat('a', 32);

        // 如果没有抛出异常，则测试通过
        AES::validateKey($key16);
        AES::validateKey($key24);
        AES::validateKey($key32);
        $this->assertTrue(true); // 测试通过
    }

    /**
     * 测试无效密钥验证抛出异常
     */
    public function testValidateKeyWithInvalidKey(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('Key length must be 16, 24, or 32 bytes');

        $invalidKey = str_repeat('a', 10);
        AES::validateKey($invalidKey);
    }

    /**
     * 测试有效IV验证
     */
    public function testValidateIvWithValidIv(): void
    {
        $iv = str_repeat('a', 16);

        // 如果没有抛出异常，则测试通过
        AES::validateIv($iv);
        $this->assertTrue(true); // 测试通过
    }

    /**
     * 测试无效IV验证抛出异常
     */
    public function testValidateIvWithInvalidIv(): void
    {
        $this->expectException(InvalidIvException::class);
        $this->expectExceptionMessage('IV length must be 16 bytes');

        $invalidIv = str_repeat('a', 10);
        AES::validateIv($invalidIv);
    }

    /**
     * 测试空IV
     */
    public function testValidateIvWithEmptyIv(): void
    {
        // 空IV应该是有效的
        AES::validateIv('');
        $this->assertTrue(true); // 测试通过
    }
}

<?php

namespace Tourze\WechatHelper\Tests\Unit;

use Tourze\WechatHelper\Exception\InvalidIvException;
use Tourze\WechatHelper\Exception\InvalidKeyException;
use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\AES;

class AESTest extends TestCase
{
    /**
     * 测试使用16字节密钥的加密功能
     */
    public function testEncrypt_withValidKey16Bytes()
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
    public function testEncrypt_withValidKey24Bytes()
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
    public function testEncrypt_withValidKey32Bytes()
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
    public function testEncrypt_withInvalidKeyLength()
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
    public function testDecrypt_withCustomMethod()
    {
        $text = 'Hello World';
        $key = str_repeat('a', 16);
        $iv = str_repeat('b', 16);
        $method = 'aes-128-cbc';

        $encrypted = openssl_encrypt($text, $method, $key, OPENSSL_RAW_DATA, $iv);
        $decrypted = AES::decrypt($encrypted, $key, $iv, OPENSSL_RAW_DATA, $method);

        $this->assertEquals($text, $decrypted);
    }

    /**
     * 测试使用无效IV长度时抛出的异常
     */
    public function testDecrypt_withInvalidIvLength()
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
    public function testGetMode_returnsCorrectMode()
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
    public function testValidateKey_withValidKey()
    {
        $key16 = str_repeat('a', 16);
        $key24 = str_repeat('a', 24);
        $key32 = str_repeat('a', 32);

        // 如果没有抛出异常，则测试通过
        $this->assertNull(AES::validateKey($key16));
        $this->assertNull(AES::validateKey($key24));
        $this->assertNull(AES::validateKey($key32));
    }

    /**
     * 测试无效密钥验证抛出异常
     */
    public function testValidateKey_withInvalidKey()
    {
        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('Key length must be 16, 24, or 32 bytes');

        $invalidKey = str_repeat('a', 10);
        AES::validateKey($invalidKey);
    }

    /**
     * 测试有效IV验证
     */
    public function testValidateIv_withValidIv()
    {
        $iv = str_repeat('a', 16);

        // 如果没有抛出异常，则测试通过
        $this->assertNull(AES::validateIv($iv));
    }

    /**
     * 测试无效IV验证抛出异常
     */
    public function testValidateIv_withInvalidIv()
    {
        $this->expectException(InvalidIvException::class);
        $this->expectExceptionMessage('IV length must be 16 bytes');

        $invalidIv = str_repeat('a', 10);
        AES::validateIv($invalidIv);
    }

    /**
     * 测试空IV
     */
    public function testValidateIv_withEmptyIv()
    {
        // 空IV应该是有效的
        $this->assertNull(AES::validateIv(''));
    }
}

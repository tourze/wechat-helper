<?php

namespace Tourze\WechatHelper\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\Encryptor;
use Tourze\WechatHelper\Exception\DecryptException;
use Tourze\WechatHelper\Exception\EncryptionException;
use Tourze\WechatHelper\Exception\InvalidAppIdException;
use Tourze\WechatHelper\Exception\InvalidBlockSizeException;
use Tourze\WechatHelper\Exception\InvalidSignatureException;

class EncryptorTest extends TestCase
{
    private string $appId;
    private string $token;
    private string $aesKey;
    private Encryptor $encryptor;

    protected function setUp(): void
    {
        $this->appId = 'test_app_id';
        $this->token = 'test_token';
        $this->aesKey = base64_encode(str_repeat('a', 32));
        $this->encryptor = new Encryptor($this->appId, $this->token, $this->aesKey);
    }

    public function testConstructor()
    {
        $encryptor = new Encryptor($this->appId, $this->token, $this->aesKey);
        $this->assertEquals($this->token, $encryptor->getToken());
    }

    public function testConstructorWithNullToken()
    {
        $encryptor = new Encryptor($this->appId, null, $this->aesKey);
        $this->assertNull($encryptor->getToken());
    }

    public function testConstructorWithNullAesKey()
    {
        $encryptor = new Encryptor($this->appId, $this->token, null);
        $this->assertEquals($this->token, $encryptor->getToken());
    }

    public function testGetToken()
    {
        $this->assertEquals($this->token, $this->encryptor->getToken());
    }

    public function testSignature()
    {
        $args = [$this->token, 'nonce', 'timestamp'];
        sort($args, SORT_STRING);
        $expected = sha1(implode($args));
        $result = $this->encryptor->signature($this->token, 'timestamp', 'nonce');
        $this->assertEquals($expected, $result);
    }

    public function testSignatureWithMultipleArgs()
    {
        $args = ['arg1', 'arg2', 'arg3'];
        sort($args, SORT_STRING);
        $expected = sha1(implode($args));
        $result = $this->encryptor->signature('arg2', 'arg1', 'arg3');
        $this->assertEquals($expected, $result);
    }

    public function testPkcs7Pad()
    {
        $text = 'hello';
        $blockSize = 16;
        $padding = $blockSize - (strlen($text) % $blockSize);
        $expected = $text . str_repeat(chr($padding), $padding);
        
        $result = $this->encryptor->pkcs7Pad($text, $blockSize);
        $this->assertEquals($expected, $result);
    }

    public function testPkcs7PadWithExactBlockSize()
    {
        $text = str_repeat('a', 16);
        $blockSize = 16;
        $expected = $text . str_repeat(chr($blockSize), $blockSize);
        
        $result = $this->encryptor->pkcs7Pad($text, $blockSize);
        $this->assertEquals($expected, $result);
    }

    public function testPkcs7PadThrowsExceptionForLargeBlockSize()
    {
        $this->expectException(InvalidBlockSizeException::class);
        $this->expectExceptionMessage('$blockSize may not be more than 256');
        
        $this->encryptor->pkcs7Pad('test', 257);
    }

    public function testPkcs7Unpad()
    {
        $text = 'hello';
        $blockSize = 16;
        $padding = $blockSize - (strlen($text) % $blockSize);
        $paddedText = $text . str_repeat(chr($padding), $padding);
        
        $result = $this->encryptor->pkcs7Unpad($paddedText);
        $this->assertEquals($text, $result);
    }

    public function testPkcs7UnpadWithInvalidPadding()
    {
        $text = 'hello' . chr(0);
        $result = $this->encryptor->pkcs7Unpad($text);
        $this->assertEquals($text, $result);
    }

    public function testDecryptDataWithValidData()
    {
        $testData = ['name' => 'test', 'value' => 123];
        $jsonData = json_encode($testData);
        
        $sessionKey = base64_encode(str_repeat('s', 16));
        $iv = base64_encode(str_repeat('i', 16));
        $encrypted = base64_encode(openssl_encrypt(
            $jsonData,
            'aes-128-cbc',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        ));
        
        $result = $this->encryptor->decryptData($sessionKey, $iv, $encrypted);
        $this->assertEquals($testData, $result);
    }

    public function testDecryptDataThrowsExceptionForInvalidJson()
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('The given payload is invalid.');
        
        $sessionKey = base64_encode(str_repeat('s', 16));
        $iv = base64_encode(str_repeat('i', 16));
        $encrypted = base64_encode(openssl_encrypt(
            'invalid json',
            'aes-128-cbc',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        ));
        
        $this->encryptor->decryptData($sessionKey, $iv, $encrypted);
    }
}
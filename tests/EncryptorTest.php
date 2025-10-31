<?php

namespace Tourze\WechatHelper\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\Encryptor;
use Tourze\WechatHelper\Exception\DecryptException;
use Tourze\WechatHelper\Exception\InvalidBlockSizeException;

/**
 * @internal
 */
#[CoversClass(Encryptor::class)]
final class EncryptorTest extends TestCase
{
    private string $appId;

    private string $token;

    private string $aesKey;

    private Encryptor $encryptor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appId = 'test_app_id';
        $this->token = 'test_token';
        $this->aesKey = rtrim(base64_encode(str_repeat('a', 32)), '=');
        $this->encryptor = new Encryptor($this->appId, $this->token, $this->aesKey);
    }

    public function testConstructor(): void
    {
        $encryptor = new Encryptor($this->appId, $this->token, $this->aesKey);
        $this->assertEquals($this->token, $encryptor->getToken());
    }

    public function testConstructorWithNullToken(): void
    {
        $encryptor = new Encryptor($this->appId, null, $this->aesKey);
        $this->assertEquals('', $encryptor->getToken());
    }

    public function testConstructorWithNullAesKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid AES key');

        new Encryptor($this->appId, $this->token, null);
    }

    public function testGetToken(): void
    {
        $this->assertEquals($this->token, $this->encryptor->getToken());
    }

    public function testSignature(): void
    {
        $args = [$this->token, 'nonce', 'timestamp'];
        sort($args, SORT_STRING);
        $expected = sha1(implode($args));
        $result = $this->encryptor->signature($this->token, 'timestamp', 'nonce');
        $this->assertEquals($expected, $result);
    }

    public function testSignatureWithMultipleArgs(): void
    {
        $args = ['arg1', 'arg2', 'arg3'];
        sort($args, SORT_STRING);
        $expected = sha1(implode($args));
        $result = $this->encryptor->signature('arg2', 'arg1', 'arg3');
        $this->assertEquals($expected, $result);
    }

    public function testPkcs7Pad(): void
    {
        $text = 'hello';
        $blockSize = 16;
        $padding = $blockSize - (strlen($text) % $blockSize);
        $expected = $text . str_repeat(chr($padding), $padding);

        $result = $this->encryptor->pkcs7Pad($text, $blockSize);
        $this->assertEquals($expected, $result);
    }

    public function testPkcs7PadWithExactBlockSize(): void
    {
        $text = str_repeat('a', 16);
        $blockSize = 16;
        $expected = $text . str_repeat(chr($blockSize), $blockSize);

        $result = $this->encryptor->pkcs7Pad($text, $blockSize);
        $this->assertEquals($expected, $result);
    }

    public function testPkcs7PadThrowsExceptionForLargeBlockSize(): void
    {
        $this->expectException(InvalidBlockSizeException::class);
        $this->expectExceptionMessage('$blockSize may not be more than 256');

        $this->encryptor->pkcs7Pad('test', 257);
    }

    public function testPkcs7Unpad(): void
    {
        $text = 'hello';
        $blockSize = 16;
        $padding = $blockSize - (strlen($text) % $blockSize);
        $paddedText = $text . str_repeat(chr($padding), $padding);

        $result = $this->encryptor->pkcs7Unpad($paddedText);
        $this->assertEquals($text, $result);
    }

    public function testPkcs7UnpadWithInvalidPadding(): void
    {
        $text = 'hello' . chr(0);
        $result = $this->encryptor->pkcs7Unpad($text);
        $this->assertEquals($text, $result);
    }

    public function testDecryptDataWithValidData(): void
    {
        $testData = ['name' => 'test', 'value' => 123];
        $jsonData = json_encode($testData);
        $this->assertIsString($jsonData);

        $sessionKey = base64_encode(str_repeat('s', 16));
        $iv = base64_encode(str_repeat('i', 16));
        $decodedSessionKey = base64_decode($sessionKey, true);
        $decodedIv = base64_decode($iv, true);
        $this->assertIsString($decodedSessionKey);
        $this->assertIsString($decodedIv);
        $encryptedData = openssl_encrypt(
            $jsonData,
            'aes-128-cbc',
            $decodedSessionKey,
            OPENSSL_RAW_DATA,
            $decodedIv
        );
        $this->assertIsString($encryptedData);
        $encrypted = base64_encode($encryptedData);

        $result = $this->encryptor->decryptData($sessionKey, $iv, $encrypted);
        $this->assertEquals($testData, $result);
    }

    public function testDecryptDataThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('The given payload is invalid.');

        $sessionKey = base64_encode(str_repeat('s', 16));
        $iv = base64_encode(str_repeat('i', 16));
        $decodedSessionKey = base64_decode($sessionKey, true);
        $decodedIv = base64_decode($iv, true);
        $this->assertIsString($decodedSessionKey);
        $this->assertIsString($decodedIv);
        $encryptedData = openssl_encrypt(
            'invalid json',
            'aes-128-cbc',
            $decodedSessionKey,
            OPENSSL_RAW_DATA,
            $decodedIv
        );
        $this->assertIsString($encryptedData);
        $encrypted = base64_encode($encryptedData);

        $this->encryptor->decryptData($sessionKey, $iv, $encrypted);
    }

    public function testEncrypt(): void
    {
        $validAesKey = rtrim(base64_encode(str_repeat('a', 32)), '=');
        $encryptor = new Encryptor($this->appId, $this->token, $validAesKey);

        $xml = '<xml><message>test</message></xml>';
        $nonce = 'test_nonce';
        $timestamp = 1234567890;

        $result = $encryptor->encrypt($xml, $nonce, $timestamp);

        $this->assertIsString($result);
        $this->assertStringContainsString('<xml>', $result);
        $this->assertStringContainsString('<Encrypt>', $result);
        $this->assertStringContainsString('<MsgSignature>', $result);
        $this->assertStringContainsString('<TimeStamp>1234567890</TimeStamp>', $result);
        $this->assertStringContainsString('test_nonce', $result);
        $this->assertStringContainsString('</xml>', $result);
    }

    public function testEncryptWithDefaultNonceAndTimestamp(): void
    {
        $validAesKey = rtrim(base64_encode(str_repeat('a', 32)), '=');
        $encryptor = new Encryptor($this->appId, $this->token, $validAesKey);

        $xml = '<xml><message>test</message></xml>';

        $result = $encryptor->encrypt($xml);

        $this->assertIsString($result);
        $this->assertStringContainsString('<xml>', $result);
        $this->assertStringContainsString('<Encrypt>', $result);
        $this->assertStringContainsString('<MsgSignature>', $result);
        $this->assertStringContainsString('<TimeStamp>', $result);
        $this->assertStringContainsString('<Nonce>', $result);
        $this->assertStringContainsString('</xml>', $result);
    }
}

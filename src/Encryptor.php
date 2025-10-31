<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tourze\WechatHelper;

use Tourze\WechatHelper\Exception\DecryptException;
use Tourze\WechatHelper\Exception\EncryptionException;
use Tourze\WechatHelper\Exception\InvalidAppIdException;
use Tourze\WechatHelper\Exception\InvalidBlockSizeException;
use Tourze\WechatHelper\Exception\InvalidSignatureException;

/**
 * Class Encryptor.
 *
 * @author overtrue <i@overtrue.me>
 */
class Encryptor
{
    public const ERROR_INVALID_SIGNATURE = -40001; // Signature verification failed

    public const ERROR_PARSE_XML = -40002; // Parse XML failed

    public const ERROR_CALC_SIGNATURE = -40003; // Calculating the signature failed

    public const ERROR_INVALID_AES_KEY = -40004; // Invalid AESKey

    public const ERROR_INVALID_APP_ID = -40005; // Check AppID failed

    public const ERROR_ENCRYPT_AES = -40006; // AES EncryptionInterface failed

    public const ERROR_DECRYPT_AES = -40007; // AES decryption failed

    public const ERROR_INVALID_XML = -40008; // Invalid XML

    public const ERROR_BASE64_ENCODE = -40009; // Base64 encoding failed

    public const ERROR_BASE64_DECODE = -40010; // Base64 decoding failed

    public const ERROR_XML_BUILD = -40011; // XML build failed

    public const ILLEGAL_BUFFER = -41003; // Illegal buffer

    /**
     * App id.
     *
     * @var string
     */
    protected $appId;

    /**
     * 应用令牌。
     *
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $aesKey;

    /**
     * 块大小。
     *
     * @var int
     */
    protected $blockSize = 32;

    /**
     * Constructor.
     */
    public function __construct(string $appId, ?string $token = null, ?string $aesKey = null)
    {
        $this->appId = $appId;
        $this->token = $token ?? '';
        $aesKeyDecoded = base64_decode($aesKey . '=', true);
        if (false === $aesKeyDecoded) {
            throw new \InvalidArgumentException('Invalid AES key');
        }
        $this->aesKey = $aesKeyDecoded;
    }

    /**
     * 获取应用令牌。
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * 加密消息并返回 XML。
     */
    public function encrypt(string $xml, ?string $nonce = null, ?int $timestamp = null): string
    {
        try {
            $xml = $this->pkcs7Pad(substr(md5(uniqid()), 0, 16) . pack('N', strlen($xml)) . $xml . $this->appId, $this->blockSize);

            $encrypted = base64_encode(AES::encrypt(
                $xml,
                $this->aesKey,
                substr($this->aesKey, 0, 16),
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
            ));
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            throw new EncryptionException($e->getMessage(), self::ERROR_ENCRYPT_AES);
        }
        // @codeCoverageIgnoreEnd

        if (null === $nonce) {
            $nonce = substr($this->appId, 0, 10);
        }
        if (null === $timestamp) {
            $timestamp = time();
        }

        $response = [
            'Encrypt' => $encrypted,
            'MsgSignature' => $this->signature($this->token, $timestamp, $nonce, $encrypted),
            'TimeStamp' => $timestamp,
            'Nonce' => $nonce,
        ];

        // 生成响应xml
        return XML::build($response);
    }

    /**
     * 解密消息。
     */
    public function decrypt(string $content, string $msgSignature, string $nonce, string $timestamp): string
    {
        $signature = $this->signature($this->token, $timestamp, $nonce, $content);

        if ($signature !== $msgSignature) {
            throw new InvalidSignatureException('Invalid Signature.', self::ERROR_INVALID_SIGNATURE);
        }

        $contentDecoded = base64_decode($content, true);
        if (false === $contentDecoded) {
            throw new DecryptException('Invalid base64 content');
        }
        $decrypted = AES::decrypt(
            $contentDecoded,
            $this->aesKey,
            substr($this->aesKey, 0, 16),
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );
        $result = $this->pkcs7Unpad($decrypted);
        $content = substr($result, 16, strlen($result));
        $unpackResult = unpack('N', substr($content, 0, 4));
        if (false === $unpackResult) {
            throw new DecryptException('Failed to unpack content length');
        }
        $contentLen = $unpackResult[1];

        if (!is_int($contentLen)) {
            throw new DecryptException('Invalid content length', self::ERROR_DECRYPT_AES);
        }
        if (trim(substr($content, $contentLen + 4)) !== $this->appId) {
            throw new InvalidAppIdException('Invalid appId.', self::ERROR_INVALID_APP_ID);
        }

        return substr($content, 4, $contentLen);
    }

    /**
     * 获取 SHA1 签名。
     */
    public function signature(): string
    {
        $array = func_get_args();
        sort($array, SORT_STRING);

        return sha1(implode($array));
    }

    /**
     * PKCS#7 pad.
     */
    public function pkcs7Pad(string $text, int $blockSize): string
    {
        if ($blockSize > 256) {
            throw new InvalidBlockSizeException('$blockSize may not be more than 256');
        }
        $padding = $blockSize - (strlen($text) % $blockSize);
        $pattern = chr($padding);

        return $text . str_repeat($pattern, $padding);
    }

    /**
     * PKCS#7 去填充。
     */
    public function pkcs7Unpad(string $text): string
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > $this->blockSize) {
            $pad = 0;
        }

        return substr($text, 0, strlen($text) - $pad);
    }

    /**
     * 解密数据。
     *
     * @return array<string, mixed>
     */
    public function decryptData(string $sessionKey, string $iv, string $encrypted): array
    {
        $encryptedDecoded = base64_decode($encrypted, true);
        $sessionKeyDecoded = base64_decode($sessionKey, true);
        $ivDecoded = base64_decode($iv, true);

        if (false === $encryptedDecoded || false === $sessionKeyDecoded || false === $ivDecoded) {
            throw new DecryptException('Invalid base64 data');
        }

        $decrypted = AES::decrypt(
            $encryptedDecoded,
            $sessionKeyDecoded,
            $ivDecoded
        );

        $decodedData = json_decode($decrypted, true);

        if (!is_array($decodedData)) {
            throw new DecryptException('The given payload is invalid.');
        }

        // Ensure we return array<string, mixed>
        $result = [];
        foreach ($decodedData as $key => $value) {
            $result[(string) $key] = $value;
        }

        return $result;
    }
}

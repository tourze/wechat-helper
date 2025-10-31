# WechatHelper

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-helper)
[![PHP Version Require](http://poser.pugx.org/tourze/wechat-helper/require/php)](https://packagist.org/packages/tourze/wechat-helper)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-helper)
[![Coverage](https://codecov.io/gh/tourze/php-monorepo/branch/master/graph/badge.svg?flag=wechat-helper)](https://codecov.io/gh/tourze/php-monorepo?flag=wechat-helper)

A PHP library providing essential helper functions for WeChat development, including AES encryption/decryption and XML processing utilities with comprehensive security validation.

## Features

- AES encryption and decryption with 128/192/256-bit key support
- WeChat message encryption/decryption for secure communication
- WeChat Mini Program data decryption support
- XML parsing with sanitization and CDATA handling
- Array to XML conversion with nested structure support
- Comprehensive exception handling for security validation
- Compatible with PHP 8.1+ and modern WeChat APIs

## Dependencies

This package requires the following PHP extensions:
- `ext-json` - JSON processing
- `ext-libxml` - XML library functionality
- `ext-openssl` - OpenSSL encryption
- `ext-simplexml` - Simple XML processing

## Installation

Install the package via Composer:

```bash
composer require tourze/wechat-helper
```

## Quick Start

### AES Encryption

```php
<?php

use Tourze\WechatHelper\AES;

// Encrypt data
$plaintext = 'Hello WeChat';
$key = 'your-16-24-32-byte-key';  // 16, 24, or 32 bytes
$iv = 'your-16-byte-iv--';        // 16 bytes
$encrypted = AES::encrypt($plaintext, $key, $iv);

// Decrypt data
$decrypted = AES::decrypt($encrypted, $key, $iv);
echo $decrypted; // Output: Hello WeChat
```

### WeChat Message Encryption

```php
<?php

use Tourze\WechatHelper\Encryptor;

$encryptor = new Encryptor('your-app-id', 'your-token', 'your-encoding-aes-key');

// Encrypt outgoing message
$message = '<xml><Content><![CDATA[Hello]]></Content></xml>';
$encrypted = $encryptor->encrypt($message, 'timestamp', 'nonce');

// Decrypt incoming message
$content = 'encrypted-xml-data';
$msgSignature = 'wechat-msg-signature';
$nonce = 'nonce';
$timestamp = 'timestamp';
$decrypted = $encryptor->decrypt($content, $msgSignature, $nonce, $timestamp);

// Decrypt WeChat Mini Program data
$sessionKey = 'your-session-key';
$encryptedData = 'encrypted-mini-program-data';
$iv = 'your-iv';
$userData = $encryptor->decryptData($sessionKey, $encryptedData, $iv);
```

### XML Processing

```php
<?php

use Tourze\WechatHelper\XML;

// Parse XML to array
$xml = '<xml><name><![CDATA[John]]></name><age>25</age></xml>';
$array = XML::parse($xml);
// Result: ['name' => 'John', 'age' => '25']

// Convert array to XML
$data = [
    'name' => 'John',
    'age' => 25,
    'items' => ['apple', 'banana']
];
$xml = XML::build($data, 'xml', 'item');
// Output: <xml><name><![CDATA[John]]></name><age>25</age>...</xml>

// Create CDATA section
$cdata = XML::cdata('Special <characters> & symbols');
// Output: <![CDATA[Special <characters> & symbols]]>
```

## Usage

### Error Handling

All encryption/decryption operations include comprehensive error handling:

```php
<?php

use Tourze\WechatHelper\Encryptor;
use Tourze\WechatHelper\Exception\InvalidSignatureException;
use Tourze\WechatHelper\Exception\DecryptException;

try {
    $encryptor = new Encryptor($appId, $token, $encodingAesKey);
    $result = $encryptor->decrypt($data, $msgSignature, $nonce, $timestamp);
} catch (InvalidSignatureException $e) {
    // Handle signature validation failure
    echo 'Invalid signature: ' . $e->getMessage();
} catch (DecryptException $e) {
    // Handle decryption failure
    echo 'Decryption failed: ' . $e->getMessage();
}
```

### Advanced Usage

#### Custom XML Root and Item Names

```php
<?php

use Tourze\WechatHelper\XML;

$data = ['item1', 'item2', 'item3'];
$xml = XML::build($data, 'root', 'element', 'id="list"', 'index');
// Output: <root id="list"><element index="0">...</element>...</root>
```

#### XML Sanitization

The XML parser automatically sanitizes input to remove invalid characters:

```php
<?php

use Tourze\WechatHelper\XML;

$invalidXml = "<xml>Invalid\x00characters</xml>";
$cleanXml = XML::sanitize($invalidXml);
// Removes null bytes and other invalid XML characters
```

## Testing

Run the test suite:

```bash
# Run all tests
./vendor/bin/phpunit packages/wechat-helper/tests

# Run tests with verbose output
./vendor/bin/phpunit packages/wechat-helper/tests --testdox

# Check test coverage (requires Xdebug)
./vendor/bin/phpunit packages/wechat-helper/tests --coverage-text
```

This package includes comprehensive tests with 61 test cases covering all major functionality and edge cases.

## Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

Based on the original work by [overtrue](https://github.com/overtrue) in the [overtrue/wechat](https://github.com/overtrue/wechat) project.
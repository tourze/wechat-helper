# WechatHelper

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-helper)
[![PHP Version Require](http://poser.pugx.org/tourze/wechat-helper/require/php)](https://packagist.org/packages/tourze/wechat-helper)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-helper)
[![代码覆盖率](https://codecov.io/gh/tourze/php-monorepo/branch/master/graph/badge.svg?flag=wechat-helper)](https://codecov.io/gh/tourze/php-monorepo?flag=wechat-helper)

一个为微信开发提供核心助手功能的 PHP 库，包括 AES 加密解密和 XML 处理工具，具备全面的安全验证功能。

## 功能特性

- 支持 128/192/256 位密钥的 AES 加密解密
- 微信消息加密解密，确保通信安全
- 微信小程序数据解密支持
- XML 解析，支持清理和 CDATA 处理
- 数组转 XML，支持嵌套结构
- 全面的异常处理，保障安全验证
- 兼容 PHP 8.1+ 和现代微信 API

## 依赖要求

此包需要以下 PHP 扩展：
- `ext-json` - JSON 处理
- `ext-libxml` - XML 库功能
- `ext-openssl` - OpenSSL 加密
- `ext-simplexml` - Simple XML 处理

## 安装

通过 Composer 安装包：

```bash
composer require tourze/wechat-helper
```

## 快速开始

### AES 加密

```php
<?php

use Tourze\WechatHelper\AES;

// 加密数据
$plaintext = 'Hello WeChat';
$key = 'your-16-24-32-byte-key';  // 16、24 或 32 字节
$iv = 'your-16-byte-iv--';        // 16 字节
$encrypted = AES::encrypt($plaintext, $key, $iv);

// 解密数据
$decrypted = AES::decrypt($encrypted, $key, $iv);
echo $decrypted; // 输出: Hello WeChat
```

### 微信消息加密

```php
<?php

use Tourze\WechatHelper\Encryptor;

$encryptor = new Encryptor('your-app-id', 'your-token', 'your-encoding-aes-key');

// 加密发送消息
$message = '<xml><Content><![CDATA[Hello]]></Content></xml>';
$encrypted = $encryptor->encrypt($message, 'timestamp', 'nonce');

// 解密接收消息
$content = 'encrypted-xml-data';
$msgSignature = 'wechat-msg-signature';
$nonce = 'nonce';
$timestamp = 'timestamp';
$decrypted = $encryptor->decrypt($content, $msgSignature, $nonce, $timestamp);

// 解密微信小程序数据
$sessionKey = 'your-session-key';
$encryptedData = 'encrypted-mini-program-data';
$iv = 'your-iv';
$userData = $encryptor->decryptData($sessionKey, $encryptedData, $iv);
```

### XML 处理

```php
<?php

use Tourze\WechatHelper\XML;

// 解析 XML 为数组
$xml = '<xml><name><![CDATA[John]]></name><age>25</age></xml>';
$array = XML::parse($xml);
// 结果: ['name' => 'John', 'age' => '25']

// 数组转换为 XML
$data = [
    'name' => 'John',
    'age' => 25,
    'items' => ['apple', 'banana']
];
$xml = XML::build($data, 'xml', 'item');
// 输出: <xml><name><![CDATA[John]]></name><age>25</age>...</xml>

// 创建 CDATA 段
$cdata = XML::cdata('特殊 <字符> & 符号');
// 输出: <![CDATA[特殊 <字符> & 符号]]>
```

## 使用方法

### 错误处理

所有加密解密操作都包含全面的错误处理：

```php
<?php

use Tourze\WechatHelper\Encryptor;
use Tourze\WechatHelper\Exception\InvalidSignatureException;
use Tourze\WechatHelper\Exception\DecryptException;

try {
    $encryptor = new Encryptor($appId, $token, $encodingAesKey);
    $result = $encryptor->decrypt($data, $msgSignature, $nonce, $timestamp);
} catch (InvalidSignatureException $e) {
    // 处理签名验证失败
    echo '签名无效: ' . $e->getMessage();
} catch (DecryptException $e) {
    // 处理解密失败
    echo '解密失败: ' . $e->getMessage();
}
```

### 高级用法

#### 自定义 XML 根元素和子元素名称

```php
<?php

use Tourze\WechatHelper\XML;

$data = ['item1', 'item2', 'item3'];
$xml = XML::build($data, 'root', 'element', 'id="list"', 'index');
// 输出: <root id="list"><element index="0">...</element>...</root>
```

#### XML 清理

XML 解析器会自动清理输入，移除无效字符：

```php
<?php

use Tourze\WechatHelper\XML;

$invalidXml = "<xml>Invalid\x00characters</xml>";
$cleanXml = XML::sanitize($invalidXml);
// 移除空字节和其他无效 XML 字符
```

## 测试

运行测试套件：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/wechat-helper/tests

# 运行测试并显示详细输出
./vendor/bin/phpunit packages/wechat-helper/tests --testdox

# 检查测试覆盖率（需要 Xdebug）
./vendor/bin/phpunit packages/wechat-helper/tests --coverage-text
```

此包包含全面的测试，共有 61 个测试用例，覆盖所有主要功能和边界情况。

## 贡献

我们欢迎贡献！请遵循以下指南：

1. Fork 仓库
2. 创建功能分支
3. 为新功能添加测试
4. 确保所有测试通过
5. 提交 Pull Request

## 许可证

此项目基于 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 致谢

基于 [overtrue](https://github.com/overtrue) 在 [overtrue/wechat](https://github.com/overtrue/wechat) 项目中的原始工作。
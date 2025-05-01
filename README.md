# WechatHelper

微信开发辅助工具类库，提供常用的加解密和 XML 处理功能。

## 安装

```bash
composer require tourze/wechat-helper
```

## 功能

本库提供以下核心功能：

### AES 加解密

用于微信加密数据的 AES 加解密支持。

```php
use Tourze\WechatHelper\AES;

// 加密
$text = '待加密文本';
$key = '16/24/32位密钥';
$iv = '16位初始向量';
$encrypted = AES::encrypt($text, $key, $iv);

// 解密
$decrypted = AES::decrypt($encrypted, $key, $iv);
```

### XML 处理

提供 XML 和数组之间转换的功能，适用于微信 API 的 XML 数据处理。

```php
use Tourze\WechatHelper\XML;

// XML 转数组
$xml = '<xml><name>test</name><value>123</value></xml>';
$array = XML::parse($xml);

// 数组转 XML
$array = [
    'name' => 'test',
    'value' => 123
];
$xml = XML::build($array);
```

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/wechat-helper/tests
```

## 许可证

本项目使用 MIT 许可证，详情请查看 [LICENSE](LICENSE) 文件。

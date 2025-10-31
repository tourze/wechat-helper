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

/**
 * Class XML.
 */
class XML
{
    /**
     * 将 XML 转换为数组。
     *
     * @param string $xml XML 字符串
     *
     * @return array<string, mixed>
     */
    public static function parse(string $xml): array
    {
        // libxml_disable_entity_loader 在 PHP 8.0+ 中已被弃用且不再需要
        $sanitizedXml = self::sanitize($xml);
        $simpleXml = simplexml_load_string($sanitizedXml, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if (false === $simpleXml) {
            throw new \InvalidArgumentException('Invalid XML string');
        }
        $normalized = self::normalize($simpleXml);
        if (!is_array($normalized)) {
            throw new \InvalidArgumentException('Normalized XML must be an array');
        }
        // Ensure array<string, mixed> format
        $result = [];
        foreach ($normalized as $key => $value) {
            $result[(string) $key] = $value;
        }

        return $result;
    }

    /**
     * 删除 XML 中的无效字符。
     *
     * @see https://www.w3.org/TR/2008/REC-xml-20081126/#charsets - XML charset range
     * @see http://php.net/manual/en/regexp.reference.escape.php - escape in UTF-8 mode
     *
     * @param string $xml
     *
     * @return string
     */
    public static function sanitize($xml): string
    {
        $result = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $xml);
        if (null === $result) {
            throw new \RuntimeException('Failed to sanitize XML string');
        }

        return $result;
    }

    /**
     * 将数组编码为 XML。
     *
     * @param mixed $data
     * @param string $root
     * @param string $item
     * @param string|array<string, string> $attr
     * @param string $id
     *
     * @return string
     */
    public static function build(
        mixed $data,
        string $root = 'xml',
        string $item = 'item',
        string|array $attr = '',
        string $id = 'id',
    ): string {
        if (is_array($attr)) {
            $_attr = [];

            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"" . (string) $value . '"';
            }

            $attr = implode(' ', $_attr);
        }

        $attr = trim($attr);
        $attr = '' === $attr ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        $dataArray = (array) $data;
        // Ensure array<string, mixed> format
        $formattedData = [];
        foreach ($dataArray as $key => $value) {
            $formattedData[(string) $key] = $value;
        }
        $xml .= self::data2Xml($formattedData, $item, $id);
        $xml .= "</{$root}>";

        return $xml;
    }

    /**
     * Build CDATA.
     *
     * @param string $string
     *
     * @return string
     */
    public static function cdata(string $string): string
    {
        return sprintf('<![CDATA[%s]>', $string);
    }

    /**
     * 将对象转换为数组。
     *
     * @param \SimpleXMLElement|mixed $obj
     *
     * @return array<string, mixed>|mixed
     */
    protected static function normalize(mixed $obj): mixed
    {
        if (is_object($obj)) {
            $obj = (array) $obj;
        }

        if (!is_array($obj)) {
            return $obj;
        }

        // Ensure array<string, mixed> format
        $formattedArray = [];
        foreach ($obj as $key => $value) {
            $formattedArray[(string) $key] = $value;
        }

        return self::normalizeArray($formattedArray);
    }

    /**
     * 规范化数组数据。
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private static function normalizeArray(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $normalizedValue = self::normalize($value);

            if ('@attributes' === $key && is_array($normalizedValue)) {
                foreach ($normalizedValue as $attrKey => $attrValue) {
                    $result[(string) $attrKey] = $attrValue;
                }
            } else {
                $result[(string) $key] = $normalizedValue;
            }
        }

        return $result;
    }

    /**
     * Array to XML.
     *
     * @param array<string, mixed> $data
     * @param string $item
     * @param string $id
     *
     * @return string
     */
    protected static function data2Xml(array $data, string $item = 'item', string $id = 'id'): string
    {
        $xml = '';

        foreach ($data as $key => $val) {
            [$elementKey, $elementAttr] = self::processXmlKey($key, $item, $id);
            $elementContent = self::processXmlValue($val, $item, $id);

            $xml .= "<{$elementKey}{$elementAttr}>{$elementContent}</{$elementKey}>";
        }

        return $xml;
    }

    /**
     * 处理 XML 键名和属性。
     *
     * @param string|int $key
     *
     * @return array{0: string, 1: string} [elementKey, elementAttributes]
     */
    private static function processXmlKey(string|int $key, string $item, string $id): array
    {
        if (is_string($key)) {
            return [$key, ''];
        }

        $attr = '' !== $id ? " {$id}=\"{$key}\"" : '';

        return [$item, $attr];
    }

    /**
     * 处理 XML 值内容。
     * @param mixed $value
     */
    private static function processXmlValue($value, string $item, string $id): string
    {
        if (is_array($value) || is_object($value)) {
            $valueArray = (array) $value;
            $formattedValue = [];
            foreach ($valueArray as $k => $v) {
                $formattedValue[(string) $k] = $v;
            }

            return self::data2Xml($formattedValue, $item, $id);
        }

        return match (true) {
            is_numeric($value) => (string) $value,
            is_string($value) => self::cdata($value),
            is_bool($value) => $value ? '1' : '0',
            null === $value => '',
            default => self::cdata(''),
        };
    }
}

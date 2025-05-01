<?php

namespace Tourze\WechatHelper\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\WechatHelper\XML;

class XMLTest extends TestCase
{
    /**
     * 测试解析有效XML
     */
    public function testParse_withValidXml()
    {
        $xml = '<xml><name>test</name><value>123</value></xml>';
        $result = XML::parse($xml);

        $expected = [
            'name' => 'test',
            'value' => '123',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * 测试解析嵌套XML
     */
    public function testParse_withNestedXml()
    {
        $xml = '<xml><user><name>test</name><age>25</age></user><status>active</status></xml>';
        $result = XML::parse($xml);

        $expected = [
            'user' => [
                'name' => 'test',
                'age' => '25',
            ],
            'status' => 'active',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * 测试解析带属性的XML
     */
    public function testParse_withAttributes()
    {
        $xml = '<xml><user id="1"><name>test</name></user></xml>';
        $result = XML::parse($xml);

        // XML解析器会把属性放在特殊的@attributes键下，但我们的normalize方法会把它转为数组的值
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertEquals('test', $result['user']['name']);
    }

    /**
     * 测试清理无效字符
     */
    public function testSanitize_removesInvalidCharacters()
    {
        // 使用一些无效的XML字符（控制字符）
        $invalidXml = "test\x00invalid\x01xml";
        $validXml = "testinvalidxml";

        $result = XML::sanitize($invalidXml);

        $this->assertEquals($validXml, $result);
    }

    /**
     * 测试从简单数组构建XML
     */
    public function testBuild_withSimpleArray()
    {
        $array = [
            'name' => 'test',
            'value' => 123,
        ];

        $xml = XML::build($array);

        // 检查生成的XML包含正确的标签和值
        $this->assertStringContainsString('<xml>', $xml);
        $this->assertStringContainsString('<name><![CDATA[test]]></name>', $xml);
        $this->assertStringContainsString('<value>123</value>', $xml);
        $this->assertStringContainsString('</xml>', $xml);
    }

    /**
     * 测试从嵌套数组构建XML
     */
    public function testBuild_withNestedArray()
    {
        $array = [
            'user' => [
                'name' => 'test',
                'age' => 25,
            ],
            'status' => 'active',
        ];

        $xml = XML::build($array);

        // 检查生成的XML包含正确的嵌套标签和值
        $this->assertStringContainsString('<xml>', $xml);
        $this->assertStringContainsString('<user>', $xml);
        $this->assertStringContainsString('<name><![CDATA[test]]></name>', $xml);
        $this->assertStringContainsString('<age>25</age>', $xml);
        $this->assertStringContainsString('</user>', $xml);
        $this->assertStringContainsString('<status><![CDATA[active]]></status>', $xml);
        $this->assertStringContainsString('</xml>', $xml);
    }

    /**
     * 测试自定义根节点
     */
    public function testBuild_withCustomRoot()
    {
        $array = [
            'name' => 'test',
            'value' => 123,
        ];

        $xml = XML::build($array, 'custom');

        $this->assertStringContainsString('<custom>', $xml);
        $this->assertStringContainsString('</custom>', $xml);
    }

    /**
     * 测试自定义项节点
     */
    public function testBuild_withCustomItem()
    {
        $array = [
            0 => 'first',
            1 => 'second',
        ];

        $xml = XML::build($array, 'xml', 'custom');

        $this->assertStringContainsString('<custom id="0"><![CDATA[first]]></custom>', $xml);
        $this->assertStringContainsString('<custom id="1"><![CDATA[second]]></custom>', $xml);
    }

    /**
     * 测试构建带属性的XML
     */
    public function testBuild_withAttributes()
    {
        $array = [
            'name' => 'test',
        ];

        $attr = ['version' => '1.0', 'encoding' => 'UTF-8'];
        $xml = XML::build($array, 'xml', 'item', $attr);

        $this->assertStringContainsString('<xml version="1.0" encoding="UTF-8">', $xml);
    }

    /**
     * 测试CDATA封装
     */
    public function testCdata_enclosesStringInCdata()
    {
        $string = 'test string';
        $result = XML::cdata($string);

        $this->assertEquals('<![CDATA[test string]]>', $result);
    }

    /**
     * 测试处理数字键的数组
     */
    public function testBuild_withNumericKeys()
    {
        $array = [
            'items' => [
                0 => 'first',
                1 => 'second',
            ],
        ];

        $xml = XML::build($array);

        $this->assertStringContainsString('<items>', $xml);
        $this->assertStringContainsString('<item id="0"><![CDATA[first]]></item>', $xml);
        $this->assertStringContainsString('<item id="1"><![CDATA[second]]></item>', $xml);
        $this->assertStringContainsString('</items>', $xml);
    }

    /**
     * 测试处理带自定义ID属性的数组
     */
    public function testBuild_withCustomIdAttribute()
    {
        $array = [
            'items' => [
                0 => 'first',
                1 => 'second',
            ],
        ];

        $xml = XML::build($array, 'xml', 'element', '', 'code');

        $this->assertStringContainsString('<items>', $xml);
        $this->assertStringContainsString('<element code="0"><![CDATA[first]]></element>', $xml);
        $this->assertStringContainsString('<element code="1"><![CDATA[second]]></element>', $xml);
        $this->assertStringContainsString('</items>', $xml);
    }

    /**
     * 测试解析包含CDATA的XML
     */
    public function testParse_withCdata()
    {
        $xml = '<xml><name><![CDATA[test]]></name><value>123</value></xml>';
        $result = XML::parse($xml);

        $expected = [
            'name' => 'test',
            'value' => '123',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * 测试解析空XML
     */
    public function testParse_withEmptyXml()
    {
        $xml = '<xml></xml>';
        $result = XML::parse($xml);

        $this->assertEmpty($result);
    }
}

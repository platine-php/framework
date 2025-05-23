<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Config;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Config\ConfigUtil;

/*
 * @group core
 * @group framework
 */
class ConfigUtilTest extends PlatineTestCase
{
    public function testConvertToDataType(): void
    {

        $this->assertIsInt(ConfigUtil::convertToDataType('1', 'integer'));
        $this->assertIsFloat(ConfigUtil::convertToDataType('1.000078', 'double'));
        $this->assertIsFloat(ConfigUtil::convertToDataType('4.078', 'float'));
        $this->assertIsArray(ConfigUtil::convertToDataType('a:1:{s:1:"a";i:3;}', 'array'));
        $this->assertTrue(ConfigUtil::convertToDataType('1', 'boolean'));
        $this->assertFalse(ConfigUtil::convertToDataType('0', 'boolean'));
    }

    public function testIsValueValidForDataType(): void
    {

        $this->assertFalse(ConfigUtil::isValueValidForDataType('1.9a', 'integer'));
        $this->assertTrue(ConfigUtil::isValueValidForDataType('1', 'integer'));
        $this->assertTrue(ConfigUtil::isValueValidForDataType('1.000078', 'double'));
        $this->assertTrue(ConfigUtil::isValueValidForDataType('4.078', 'float'));
        $this->assertTrue(ConfigUtil::isValueValidForDataType('a:1:{s:1:"a";i:3;}', 'array'));
        $this->assertTrue(ConfigUtil::isValueValidForDataType('1', 'boolean'));
        $this->assertFalse(ConfigUtil::isValueValidForDataType('0', 'boolean'));
    }
}

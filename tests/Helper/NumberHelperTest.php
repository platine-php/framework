<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\NumberHelper;

class NumberHelperTest extends PlatineTestCase
{
    public function testIsEqual(): void
    {
        $this->assertFalse(NumberHelper::isEqual(6.04, 6.0));
        $this->assertTrue(NumberHelper::isEqual(6.0, 6.0));
        $this->assertTrue(NumberHelper::isEqual(-5.0, -5.0));
        $this->assertTrue(NumberHelper::isEqual(6.0, 6.000001));
        $this->assertTrue(NumberHelper::isEqual(6.001, 6.000001));
        $this->assertTrue(NumberHelper::isEqual(6, 6)); // auto cast
    }

    public function testIsNotEqual(): void
    {
        $this->assertTrue(NumberHelper::isNotEqual(6.0, 6.1));
        $this->assertTrue(NumberHelper::isNotEqual(6.0, 7.0));
        $this->assertTrue(NumberHelper::isNotEqual(6.04, 6.0));
        $this->assertTrue(NumberHelper::isNotEqual(-6.0, 6.0));
        $this->assertTrue(NumberHelper::isNotEqual(6, 7.0)); // auto cast
    }

    public function testIsLessThan(): void
    {
        $this->assertTrue(NumberHelper::isLessThan(6.0, 6.1));
        $this->assertTrue(NumberHelper::isLessThan(5.0, 6.1));
        $this->assertTrue(NumberHelper::isLessThan(-5.0, -0.1));
        $this->assertTrue(NumberHelper::isLessThan(1.0004, 1.005));
        $this->assertTrue(NumberHelper::isLessThan(1.004, 1.05));
        $this->assertFalse(NumberHelper::isLessThan(1.0004, 1.0005));
    }

    public function testIsGreaterThan(): void
    {
        $this->assertFalse(NumberHelper::isGreaterThan(6.0, 6.1));
        $this->assertFalse(NumberHelper::isGreaterThan(5.0, 6.1));
        $this->assertFalse(NumberHelper::isGreaterThan(-5.0, -0.1));
        $this->assertFalse(NumberHelper::isGreaterThan(1.0004, 1.005));
        $this->assertFalse(NumberHelper::isGreaterThan(1.004, 1.05));
        $this->assertTrue(NumberHelper::isGreaterThan(1.005, 1.0004));
    }

    public function testNumberToString(): void
    {
        $this->assertEquals('33', NumberHelper::numberToString(33.0));
        $this->assertEquals('3.3', NumberHelper::numberToString(33e-1));
        $this->assertEquals('0.000000078', NumberHelper::numberToString(0.000000078));
    }

    public function testFloatToString(): void
    {
        $this->assertEquals('201421700079000', NumberHelper::floatToString(2.01421700079E+14));
        $this->assertEquals('33', NumberHelper::floatToString(33.0));
        $this->assertEquals('3.3', NumberHelper::floatToString(33e-1));
        $this->assertEquals('330000', NumberHelper::floatToString(33e4));
        $this->assertEquals('0.00000078', NumberHelper::floatToString(0.00000078));
        $this->assertEquals('0.0000030', NumberHelper::floatToString(0.000003));
        $this->assertEquals('0.0000123', NumberHelper::floatToString(1.23E-5));
        $this->assertEquals('1.23', NumberHelper::floatToString(1.23));
    }
}

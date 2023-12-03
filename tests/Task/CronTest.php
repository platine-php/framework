<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Task;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Task\Cron;

/*
 * @group core
 * @group framework
 */
class CronTest extends PlatineTestCase
{
    public function testParseInvalidExpression(): void
    {
        $expression = '* * * * * *';

        $this->expectException(InvalidArgumentException::class);
        Cron::parse($expression);
    }

    public function testParseWrongNumber(): void
    {
        $expression = '* * * * 7';
        $current = time();
        $time = Cron::parse($expression, $current);
        $date = date('Y-m-d H:i', $time);

        $this->assertEquals('1970-01-01 00:00', $date);
    }

    public function testParseEvery5Minute(): void
    {
        $expression = '*/5 * * * *';

        $time = Cron::parse($expression, 0);
        $date = date('Y-m-d H:i', $time);
        $expected = '1970-01-01 00:00';

        $this->assertEquals($expected, $date);
    }

    public function testParseSuccess(): void
    {
        $expression = '*/11 1-6 1,4 * *';

        $time = Cron::parse($expression, 0);
        $date = date('Y-m-d H:i', $time);
        $expected = '1970-01-01 01:00';

        $this->assertEquals($expected, $date);
    }

    public function testParseZero(): void
    {
        global $mock_preg_split_to_false;
        $mock_preg_split_to_false = true;
        $expression = '*/11 1-6 1,4 * *';

        $time = Cron::parse($expression, 0);
        $date = date('Y-m-d H:i', $time);
        $expected = '1970-01-01 00:00';

        $this->assertEquals($time, 0);
        $this->assertEquals($expected, $date);
    }
}

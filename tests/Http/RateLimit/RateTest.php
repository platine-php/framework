<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\RateLimit;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RateLimit\Rate;

/*
 * @group core
 * @group framework
 */
class RateTest extends PlatineTestCase
{
    public function testInvaliValueOfQuota(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quota must be greater than zero, received [0]');

        Rate::perSecond(0);
    }

    public function testInvaliValueOfInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Seconds interval must be greater than zero, received [-1]');

        Rate::custom(10, -1);
    }

    /**
     * @dataProvider commonDataProvider
     * @param string $method method to call
     * @param array<int, mixed> $args methods arguments
     * @param int $expectedQuota
     * @param int $expectedInterval
     * @return void
     */
    public function testCommonMethods(
        string $method,
        array $args,
        int $expectedQuota,
        int $expectedInterval
    ): void {
        /** @var Rate $rate */
        $rate = Rate::{$method}(...$args);
        $this->assertEquals($expectedQuota, $rate->getQuota());
        $this->assertEquals($expectedInterval, $rate->getInterval());
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'perSecond',
              [13],
              13,
              1
          ],
          [
              'perMinute',
              [80],
              80,
              60
          ],
          [
              'perHour',
              [17],
              17,
              3600
          ],
          [
              'perDay',
              [3],
              3,
              86400
          ],
          [
              'custom',
              [13, 50],
              13,
              50
          ],

        ];
    }
}

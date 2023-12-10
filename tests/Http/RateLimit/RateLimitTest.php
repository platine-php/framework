<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\RateLimit;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RateLimit\Exception\LimitExceededException;
use Platine\Framework\Http\RateLimit\Rate;
use Platine\Framework\Http\RateLimit\RateLimit;
use Platine\Framework\Http\RateLimit\Storage\InMemoryStorage;

/*
 * @group core
 * @group framework
 */
class RateLimitTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $storage = $this->getMockInstance(InMemoryStorage::class);
        $o = new RateLimit($storage, Rate::perDay(100), 'api');

        $this->assertInstanceOf(RateLimit::class, $o);
    }

    public function testLimitFirst(): void
    {
        $storage = $this->getMockInstance(InMemoryStorage::class, [
            'exists' => false
        ]);
        $o = new RateLimit($storage, Rate::perDay(100), 'api');

        $storage->expects($this->exactly(2))
                ->method('set');

        $o->limit('client_ip');
    }

    public function testGetRemainingAttempts(): void
    {
        $storage = $this->getMockInstance(InMemoryStorage::class, [
            'exists' => false
        ]);
        $o = new RateLimit($storage, Rate::perDay(100), 'api');

        $this->assertEquals(100, $o->getRemainingAttempts('client_ip'));
    }

    public function testLimitQuotaLimitReached(): void
    {
        $storage = new InMemoryStorage();
        $o = new RateLimit($storage, Rate::perHour(3), 'api');

        $this->assertEquals(3, $o->getRemainingAttempts('client_ip'));

        $o->limit('client_ip');
        $o->limit('client_ip');

        $this->assertEquals(1, $o->getRemainingAttempts('client_ip'));

        $this->expectException(LimitExceededException::class);
        $this->expectExceptionMessage('Limit has been exceeded for identifier "client_ip"');

        $o->limit('client_ip', 8);
    }

    public function testLimitCalculatedQuotaExceedTheConfig(): void
    {
        $storage = $this->getMockInstance(InMemoryStorage::class, [
            'exists' => true,
            'get' => 999999,
        ]);
        $o = new RateLimit($storage, Rate::perHour(3), 'api');

        $this->assertEquals(999999, $o->getRemainingAttempts('client_ip'));

        $o->limit('client_ip');
        $o->limit('client_ip');

        $this->assertEquals(999999, $o->getRemainingAttempts('client_ip'));
    }


    public function testPurge(): void
    {
        $storage = new InMemoryStorage();
        $o = new RateLimit($storage, Rate::perDay(3), 'api');

        $this->assertEquals(3, $o->getRemainingAttempts('client_ip'));

        $o->limit('client_ip');
        $o->limit('client_ip');

        $this->assertEquals(1, $o->getRemainingAttempts('client_ip'));

        $o->purge('client_ip');
        $this->assertEquals(3, $o->getRemainingAttempts('client_ip'));
    }
}

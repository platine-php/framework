<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper\Timer;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Timer\Watch;

class WatchTest extends PlatineTestCase
{
    public function testCommon(): void
    {
        global $mock_microtime_to_1;

        $mock_microtime_to_1 = true;

        $o = new Watch();
        $this->assertTrue($o->start());
        $this->assertTrue($o->exists(Watch::WATCH_DEFAULT_NAME));
        $this->assertEquals(1, $o->count());

        $this->assertEquals(0.0, $o->getTime());
        $this->assertTrue($o->pause());
        $this->assertFalse($o->pause('not_found_name'));
        $this->assertEquals(0.0, $o->getTime());
        $this->assertTrue($o->stop());
        $this->assertFalse($o->stop('not_found_name'));
        $this->assertEquals(0.0, $o->getTime());
        $this->assertEquals(-1, $o->getTime('not_found_name'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Watch with the name [not_found_name] does not exist');
        $o->getWatch('not_found_name');
    }

    public function testStartAlready(): void
    {
        $o = new Watch();
        $this->assertTrue($o->start('bootstrap'));
        $this->assertTrue($o->exists('bootstrap'));
        $this->assertEquals(1, $o->count());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Watch with the name [bootstrap] already exist');
        $o->start('bootstrap');
    }
}

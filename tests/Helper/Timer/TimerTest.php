<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper\Timer;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Timer\Timer;

class TimerTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $o = new Timer('foo');
        $this->assertEquals('foo', $o->getName());
        $this->assertEquals(Timer::NOT_STARTED, $o->getState());
        $this->assertEquals(0, $o->getTime());
    }

    public function testStartPauseStop(): void
    {
        global $mock_microtime_to_1;

        $mock_microtime_to_1 = true;

        $o = new Timer('foo');
        $o->start();
        $this->assertEquals('foo', $o->getName());
        $this->assertEquals(Timer::STARTED, $o->getState());
        $this->assertEquals(0, $o->getTime());

        $o->stop();
        $this->assertEquals(0, $o->getTime());
        $this->assertEquals(Timer::STOPPED, $o->getState());

        $o->pause();
        $this->assertEquals(0, $o->getTime());
        $this->assertEquals(Timer::STOPPED, $o->getState());

        $o->stop();
        $this->assertEquals(0, $o->getTime());
        $this->assertEquals(Timer::STOPPED, $o->getState());
    }

    public function testStartAlready(): void
    {
        $o = new Timer('foo');
        $this->assertTrue($o->start());
        $this->assertFalse($o->start());
    }

    public function testPauseNotStart(): void
    {
        $o = new Timer('foo');
        $this->assertTrue($o->start());
        $this->assertTrue($o->pause());
        $this->assertFalse($o->pause());
    }

    public function testSetInvalidState(): void
    {
        $o = new Timer('foo');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid state [-1] must be between [0-3]');

        $this->runPrivateProtectedMethod($o, 'setState', [-1]);
    }
}

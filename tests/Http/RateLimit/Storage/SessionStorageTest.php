<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\RateLimit\Storage;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RateLimit\Storage\SessionStorage;
use Platine\Session\Session;

/*
 * @group core
 * @group framework
 */
class SessionStorageTest extends PlatineTestCase
{
    public function testCreate(): void
    {
        $session = $this->getMockInstance(Session::class);
        $o = new SessionStorage($session);

        $this->assertInstanceOf(SessionStorage::class, $o);
    }

    public function testSet(): void
    {
        $session = $this->getMockInstance(Session::class);

        $session->expects($this->exactly(1))
                ->method('set');

        $o = new SessionStorage($session);

        $o->set('foo', 100, 600);
    }

    public function testGetNotFound(): void
    {
        $session = $this->getMockInstance(Session::class, [
            'get' => [],
            'has' => false
        ]);
        $o = new SessionStorage($session);

        $this->assertEquals(0, $o->get('foo'));
        $this->assertFalse($o->exists('foo'));
    }

    public function testGet(): void
    {
        $session = $this->getMockInstance(Session::class, [
            'get' => ['expire' => time() + 1000, 'value' => 108],
             'has' => true
        ]);
        $o = new SessionStorage($session);

        $this->assertEquals(108.0, $o->get('foo'));
        $this->assertTrue($o->exists('foo'));
    }

    public function testDelete(): void
    {
        $session = $this->getMockInstance(Session::class);

        $session->expects($this->exactly(1))
                ->method('remove');

        $o = new SessionStorage($session);

        $o->delete('foo');
    }
}

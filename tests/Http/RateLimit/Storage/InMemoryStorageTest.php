<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\RateLimit\Storage;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RateLimit\Storage\InMemoryStorage;

/*
 * @group core
 * @group framework
 */
class InMemoryStorageTest extends PlatineTestCase
{
    public function testCreate(): void
    {
        $o = new InMemoryStorage();

        $this->assertInstanceOf(InMemoryStorage::class, $o);
    }

    public function testAll(): void
    {
        $o = new InMemoryStorage();

        $this->assertEquals(0, $o->get('foo'));
        $this->assertFalse($o->exists('foo'));
        $o->set('foo', 100, 600);
        $this->assertEquals(100, $o->get('foo'));
        $this->assertTrue($o->exists('foo'));
        $this->assertTrue($o->delete('foo'));
        $this->assertEquals(0, $o->get('foo'));
        $this->assertFalse($o->exists('foo'));
    }
}

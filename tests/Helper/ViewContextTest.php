<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\ViewContext;

class ViewContextTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $o = new ViewContext();
        $this->assertCount(0, $o->all());
        $this->assertNull($o->get('foo'));

        $o->set('foo', 'bar');
        $this->assertCount(1, $o->all());
        $this->assertEquals('bar', $o->get('foo'));
        $this->assertArrayHasKey('foo', $o->all());
    }

    public function testAllUsingArrayAccess(): void
    {
        $o = new ViewContext();
        $this->assertCount(0, $o->all());
        $this->assertNull($o['foo']);

        $o['foo'] = 'bar';
        $this->assertCount(1, $o->all());
        $this->assertEquals('bar', $o['foo']);
        $this->assertArrayHasKey('foo', $o);

        unset($o['foo']);
        $this->assertNull($o['foo']);
    }
}

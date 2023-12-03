<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Csrf\Storage;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\Csrf\Storage\CsrfNullStorage;

/*
 * @group core
 * @group security
 */
class CsrfNullStorageTest extends PlatineTestCase
{
    public function testGetNull(): void
    {
        $o = new CsrfNullStorage();

        $this->assertNull($o->get('token'));
    }

    public function testAll(): void
    {
        $o = new CsrfNullStorage();

        $this->assertNull($o->get('token'));

        $o->set('token', 'foobar', 100);

        $res = $o->get('token');
        $this->assertEquals('foobar', $res);
        $o->delete('token');

        $this->assertNull($o->get('token'));

        $o->clear();
        $this->assertNull($o->get('token'));
    }
}

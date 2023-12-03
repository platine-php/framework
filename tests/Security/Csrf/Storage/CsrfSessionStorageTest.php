<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Csrf\Storage;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\Csrf\Storage\CsrfSessionStorage;
use Platine\Session\Session;

/*
 * @group core
 * @group security
 */
class CsrfSessionStorageTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $session = new Session();

        $o = new CsrfSessionStorage($session);

        $this->assertInstanceOf(CsrfSessionStorage::class, $o);
    }

    public function testGetNull(): void
    {
        $session = new Session();

        $o = new CsrfSessionStorage($session);

        $this->assertNull($o->get('token'));
    }

    public function testGetDeleteClearSuccess(): void
    {
        $_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY] = ['token' => [
            'expire' => time() + 100,
            'value' => 'foobar',
        ]];
        $session = new Session();

        $o = new CsrfSessionStorage($session);

        $res = $o->get('token');
        $this->assertEquals('foobar', $res);
        $this->assertCount(1, $_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY]);
        $o->delete('token');

        $this->assertNull($o->get('token'));
        $this->assertIsArray($_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY]);
        $this->assertCount(0, $_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY]);

        $o->clear();
        $this->assertNull($o->get('token'));
        $this->assertArrayNotHasKey(CsrfSessionStorage::CSRF_SESSION_KEY, $_SESSION);
    }

    public function testSet(): void
    {
        $session = new Session();

        $o = new CsrfSessionStorage($session);

        $this->assertNull($o->get('token'));
        $this->assertArrayNotHasKey(CsrfSessionStorage::CSRF_SESSION_KEY, $_SESSION);

        $o->set('token', 'foobar', time() + 100);

        $res = $o->get('token');
        $this->assertEquals('foobar', $res);
        $this->assertCount(1, $_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY]);
        $o->delete('token');

        $this->assertNull($o->get('token'));
        $this->assertIsArray($_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY]);
        $this->assertCount(0, $_SESSION[CsrfSessionStorage::CSRF_SESSION_KEY]);

        $o->clear();
        $this->assertNull($o->get('token'));
        $this->assertArrayNotHasKey(CsrfSessionStorage::CSRF_SESSION_KEY, $_SESSION);
    }
}

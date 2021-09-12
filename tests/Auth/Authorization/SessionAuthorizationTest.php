<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Authorization;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authorization\SessionAuthorization;
use Platine\Session\Session;

/*
 * @group core
 * @group framework
 */
class SessionAuthorizationTest extends PlatineTestCase
{

    public function testGetPermissions(): void
    {
        $session = $this->getMockInstance(Session::class, [
            'get' => ['foo', 'bar']
        ]);
        $o = new SessionAuthorization($session);
        $res = $o->getPermissions();
        $this->assertCount(2, $res);
        $this->assertEquals('foo', $res[0]);
        $this->assertEquals('bar', $res[1]);
    }

    public function testIsGranted(): void
    {
        $session = $this->getMockInstance(Session::class, [
            'get' => ['foo', 'bar']
        ]);
        $o = new SessionAuthorization($session);
        $this->assertTrue($o->isGranted('foo'));
        $this->assertTrue($o->isGranted('bar'));
        $this->assertFalse($o->isGranted('baz'));
    }
}

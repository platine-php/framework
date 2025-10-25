<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Authorization;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\Authorization\DefaultAuthorization;

/*
 * @group core
 * @group framework
 */
class DefaultAuthorizationTest extends PlatineTestCase
{
    public function testIsGranted(): void
    {
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'getPermissions' => ['foo', 'bar']
        ]);
        $o = new DefaultAuthorization($authentication);
        $this->assertTrue($o->isGranted('foo'));
        $this->assertTrue($o->isGranted('bar'));
        $this->assertFalse($o->isGranted('baz'));
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Event;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Event\AuthInvalidPasswordEvent;

/*
 * @group core
 * @group framework
 */
class AuthInvalidPasswordEventTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $user = $this->getMockInstance(User::class);
        $userSet = $this->getMockInstance(User::class);
        $o = new AuthInvalidPasswordEvent($user);

        $this->assertInstanceOf(User::class, $o->getUser());
        $this->assertEquals($user, $o->getUser());
        $o->setUser($userSet);
        $this->assertInstanceOf(User::class, $o->getUser());
        $this->assertEquals($userSet, $o->getUser());
    }
}

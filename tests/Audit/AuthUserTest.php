<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\AuthUser;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\Entity\User;

/*
 * @group core
 * @group framework
 */
class AuthUserTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $user = $this->getMockInstance(User::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'getUser' => $user
        ]);
        $o = new AuthUser($authentication);

        $this->assertInstanceOf(AuthUser::class, $o);
    }

    public function testGet(): void
    {

        $user = $this->getMockInstance(User::class, [
            'getId' => 123
        ]);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'getUser' => $user
        ]);
        $o = new AuthUser($authentication);

        $this->assertEquals(123, $o->getUserId());
    }
}

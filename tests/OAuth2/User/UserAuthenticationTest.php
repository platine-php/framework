<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\User;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Exception\AuthenticationException;
use Platine\Framework\OAuth2\User\UserAuthentication;
use Platine\OAuth2\Entity\TokenOwnerInterface;

/*
 * @group core
 * @group framework
 */
class UserAuthenticationTest extends PlatineTestCase
{
    public function testValidateFailed(): void
    {
        $auth = $this->getMockInstance(SessionAuthentication::class, [
            'login' => false
        ]);

        $o = new UserAuthentication($auth);
        $this->assertNull($o->validate('demo', 'demo'));
    }

    public function testValidateFailedWithException(): void
    {
        $auth = $this->getMockBuilder(AuthenticationInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $auth->expects($this->any())
                ->method('login')
                ->willThrowException(new AuthenticationException());

        $o = new UserAuthentication($auth);
        $this->assertNull($o->validate('demo', 'demo'));
    }

    public function testValidateSuccess(): void
    {
        $user = $this->getMockInstance(User::class, [
            'getId' => 10
        ]);

        $auth = $this->getMockInstance(SessionAuthentication::class, [
            'login' => true,
            'getUser' => $user,
        ]);

        $o = new UserAuthentication($auth);
        $this->assertInstanceOf(TokenOwnerInterface::class, $o->validate('demo', 'demo'));
        $this->assertEquals(10, $o->validate('demo', 'demo')->getOwnerId());
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Authentication;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Entity\Role;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Orm\Repository;
use Platine\Security\Hash\BcryptHash;
use Platine\Session\Session;

/*
 * @group core
 * @group framework
 */
class SessionAuthenticationTest extends PlatineTestCase
{
    public function testGetUserNotLogged(): void
    {
        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => false
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(AccountNotFoundException::class);
        $o->getUser();
    }

    public function testGetUserNotFound(): void
    {
        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'find' => null
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(AccountNotFoundException::class);
        $o->getUser();
    }

    public function testGetUserSuccess(): void
    {
        $user = $this->getMockInstance(User::class);
        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'find' => $user
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->assertInstanceOf(User::class, $o->getUser());
    }

    public function testLoginUsernameEmpty(): void
    {
        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'find' => null
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(MissingCredentialsException::class);

        $credentials = [
            'password' => 'foo'
        ];
        $o->login($credentials);
    }

    public function testLoginPasswordEmpty(): void
    {
        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'find' => null
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(MissingCredentialsException::class);

        $credentials = [
            'username' => 'foo'
        ];
        $o->login($credentials);
    }

    public function testLoginUserNotFound(): void
    {
        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'findBy' => null
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(AccountNotFoundException::class);

        $credentials = [
            'username' => 'foo',
            'password' => 'foo',
        ];
        $o->login($credentials);
    }

    public function testLoginUserIsLocked(): void
    {
        $user = $this->getMockInstance(User::class, [
            '__get' => 'D'
        ]);
        $middleRepository = $this->getMockInstance(Repository::class, [
            'findBy' => $user
        ]);

        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'with' => $middleRepository,
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(AccountLockedException::class);

        $credentials = [
            'username' => 'foo',
            'password' => 'foo',
        ];
        $o->login($credentials);
    }

    public function testLoginWrongPassword(): void
    {
        $user = $this->getMockInstance(User::class, [
            '__get' => 'A'
        ]);
        $middleRepository = $this->getMockInstance(Repository::class, [
            'findBy' => $user
        ]);

        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class, [
            'verify' => false
        ]);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'with' => $middleRepository,
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $this->expectException(InvalidCredentialsException::class);

        $credentials = [
            'username' => 'foo',
            'password' => 'foo',
        ];
        $o->login($credentials);
    }

    public function testLoginSuccess(): void
    {
        $permission = $this->getMockInstanceMap(Permission::class, [
            '__get' => [
                ['code', 'foocode']
            ]
        ]);

        $role = $this->getMockInstanceMap(Role::class, [
            '__get' => [
                ['permissions', [$permission]]
            ]
        ]);
        $user = $this->getMockInstanceMap(User::class, [
            '__get' => [
                ['password', 'password'],
                ['status', 'A'],
                ['roles', [$role]]
             ]
        ]);
        $middleRepository = $this->getMockInstance(Repository::class, [
            'findBy' => $user
        ]);

        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class, [
            'verify' => true
        ]);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'with' => $middleRepository,
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $credentials = [
            'username' => 'foo',
            'password' => 'foo',
        ];
        $this->assertTrue($o->login($credentials));
    }

    public function testLogout(): void
    {
        global $mock_session_unset, $mock_session_destroy;

        $mock_session_unset = true;
        $mock_session_destroy = true;

        $app = $this->getMockInstance(Application::class);
        $hash = $this->getMockInstance(BcryptHash::class, [
            'verify' => false
        ]);
        $session = $this->getMockInstance(Session::class, [
            'has' => true
        ]);

        $session->expects($this->exactly(1))
                ->method('remove');

        $userRepository = $this->getMockInstance(UserRepository::class, [
        ]);

        $o = new SessionAuthentication($app, $hash, $session, $userRepository);
        $o->logout();
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Authentication;

use DateTime;
use Platine\Config\Config;
use Platine\Container\Container;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authentication\JWTAuthentication;
use Platine\Framework\Auth\Authorization\Cache\NullCacheStorage;
use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Entity\Role;
use Platine\Framework\Auth\Entity\Token;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Auth\Repository\TokenRepository;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Security\JWT\Exception\JWTException;
use Platine\Framework\Security\JWT\JWT;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\Logger;
use Platine\Orm\Repository;
use Platine\Security\Hash\BcryptHash;

/*
 * @group core
 * @group framework
 */
class JWTAuthenticationTest extends PlatineTestCase
{
    public function testGetUserFailed(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);

        $userRepository = $this->getMockInstance(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->expectException(AccountNotFoundException::class);
        $o->getUser();
    }

    public function testIsLoggedServerRequestNotAvailable(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, false],
            ],
        ]);

        $userRepository = $this->getMockInstance(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->assertFalse($o->isLogged());
    }

    public function testGetUserFailedWrongJWTToken(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $jwt->expects($this->exactly(1))
                ->method('decode')
                ->will($this->throwException(new JWTException()));

        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '7676ghggfhfgfghg']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->expectException(AccountNotFoundException::class);
        $o->getUser();
    }


    public function testGetUserNotFound(): void
    {
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['sub' => 1]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '7676ghggfhfgfghg']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class, [
            'find' => [
                [1, null]
            ]
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->expectException(AccountNotFoundException::class);
        $o->getUser();
    }

    public function testGetIdSuccess(): void
    {
        $user = $this->getMockInstance(User::class);
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['sub' => 1]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '7676ghggfhfgfghg']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class, [
            'find' => [
                [1, $user]
            ]
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
        $this->assertEquals(1, $o->getId());
    }

    public function testGetIdNotLogged(): void
    {
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['sub' => 1]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class, [
            'find' => [
                [1, null]
            ]
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->expectException(AccountNotFoundException::class);
        $o->getId();
    }

    public function testGetUserSuccess(): void
    {
        $user = $this->getMockInstance(User::class);
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['sub' => 1]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '7676ghggfhfgfghg']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class, [
            'find' => [
                [1, $user]
            ]
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->assertInstanceOf(User::class, $o->getUser());
    }

    public function testGetPermissions(): void
    {
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['roles' => ['1', '2']]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '7676ghggfhfgfghg']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class, [
            'get' => ['user_create', 'user_delete']
        ]);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->assertCount(2, $o->getPermissions());
    }

    public function testGetPermissionsFromDB(): void
    {
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['roles' => ['1', '2']]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '7676ghggfhfgfghg']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class, [
            'get' => []
        ]);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->assertCount(0, $o->getPermissions());
    }

    public function testGetPermissionsUserNotLogged(): void
    {
        $jwt = $this->getMockInstance(JWT::class, [
            'getPayload' => ['permissions' => ['user_create', 'user_update']]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', '', 'foosecret'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstanceMap(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $this->assertCount(0, $o->getPermissions());
    }

    public function testLoginUsernameEmpty(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
        $this->expectException(MissingCredentialsException::class);

        $credentials = [
            'password' => 'foo'
        ];
        $o->login($credentials);
    }

    public function testLoginPasswordEmpty(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
        $this->expectException(MissingCredentialsException::class);

        $credentials = [
            'username' => 'foo'
        ];
        $o->login($credentials);
    }

    public function testLogout(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
        $o->logout(true);

        $this->assertTrue(true);
    }

    public function testLoginUserNotFound(): void
    {
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'findBy' => null
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
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
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'with' => $middleRepository,
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
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
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
            ]
        ]);
        $tokenRepository = $this->getMockInstance(TokenRepository::class);
        $hash = $this->getMockInstance(BcryptHash::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'with' => $middleRepository,
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );
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

        $dt = new DateTime();

        $token = $this->getMockInstanceMap(Token::class, [
            '__get' => [
                ['expire_at', $dt]
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
        $jwt = $this->getMockInstance(JWT::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.headers.name', 'Authorization', 'Authorization'],
                ['api.auth.headers.token_type', 'Bearer', 'Bearer'],
                ['api.sign.secret', null, 'foosecret'],
                ['api.auth.token_expire', 900, 900],
                ['api.auth.refresh_token_expire', 30 * 86400, 900],
            ]
        ]);

        $tokenRepository = $this->getMockInstance(TokenRepository::class, [
            'create' => $token
        ]);
        $hash = $this->getMockInstance(BcryptHash::class, [
            'verify' => true
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Authorization', '']
            ]
        ]);
        $containter = $this->getMockInstanceMap(Container::class, [
            'has' => [
                [ServerRequestInterface::class, true],
            ],
            'get' => [
                [ServerRequestInterface::class, $request],
            ],
        ]);
        $userRepository = $this->getMockInstance(UserRepository::class, [
            'with' => $middleRepository,
        ]);
        $cacheStorage = $this->getMockInstance(NullCacheStorage::class);

        $o = new JWTAuthentication(
            $jwt,
            $logger,
            $config,
            $hash,
            $userRepository,
            $tokenRepository,
            $containter,
            $cacheStorage
        );

        $credentials = [
            'username' => 'foo',
            'password' => 'foo',
        ];

        $data = $o->login($credentials);
        $this->assertCount(4, $data);
    }
}

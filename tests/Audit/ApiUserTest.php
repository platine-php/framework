<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\ApiUser;
use Platine\Framework\Auth\Authentication\JWTAuthentication;
use Platine\Framework\Auth\Entity\User;

/*
 * @group core
 * @group framework
 */
class ApiUserTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $user = $this->getMockInstance(User::class);
        $authentication = $this->getMockInstance(JWTAuthentication::class, [
            'getUser' => $user
        ]);
        $o = new ApiUser($authentication);

        $this->assertInstanceOf(ApiUser::class, $o);
    }

    public function testGet(): void
    {

        $user = $this->getMockInstance(User::class, [
            'getId' => 123
        ]);
        $authentication = $this->getMockInstance(JWTAuthentication::class, [
            'getUser' => $user
        ]);
        $o = new ApiUser($authentication);

        $this->assertEquals(123, $o->getUserId());
    }

}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Audit\Model\AuditRepository;
use Platine\Framework\Audit\SessionUser;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\Entity\User;

/*
 * @group core
 * @group framework
 */
class SessionUserTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $user = $this->getMockInstance(User::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'getUser' => $user
        ]);
        $o = new SessionUser($authentication);

        $this->assertInstanceOf(SessionUser::class, $o);
    }

    public function testGet(): void
    {

        $user = $this->getMockInstance(User::class, [
            'getId' => 123
        ]);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'getUser' => $user
        ]);
        $o = new SessionUser($authentication);

        $this->assertEquals(123, $o->getUserId());
    }
}

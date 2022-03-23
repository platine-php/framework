<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Audit\Model\AuditRepository;
use Platine\Framework\Auth\Entity\User;

/*
 * @group core
 * @group framework
 */
class AuditorTest extends PlatineTestCase
{
    public function testConstructor(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class);

        $o = new Auditor($repository);

        $this->assertInstanceOf(AuditRepository::class, $o->getRepository());
        $this->assertEquals($repository, $o->getRepository());
    }


    public function testLogUserNotProvided(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class);

        $o = new Auditor($repository);

        $this->expectException(InvalidArgumentException::class);
        $o->log([]);
    }

    public function testLogUserIsNotAnUserEntity(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class);

        $o = new Auditor($repository);

        $this->expectException(InvalidArgumentException::class);
        $o->log([
            'user' => 344
        ]);
    }

    public function testLogSuccess(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class, [
            'save' => true
        ]);

        $user = $this->getMockInstance(User::class);

        $o = new Auditor($repository);


        $result = $o->log([
            'user' => $user
        ]);

        $this->assertTrue($result);
    }

    public function testLogError(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class, [
            'save' => false
        ]);

        $user = $this->getMockInstance(User::class);

        $o = new Auditor($repository);


        $result = $o->log([
            'user' => $user
        ]);

        $this->assertFalse($result);
    }
}

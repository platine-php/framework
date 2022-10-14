<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Audit\Model\AuditRepository;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Http\ServerRequest;
use Platine\UserAgent\UserAgent;

/*
 * @group core
 * @group framework
 */
class AuditorTest extends PlatineTestCase
{
    public function testConstructor(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $request,
            $userAgent,
            $authentication,
            $userRepository
        );

        $this->assertInstanceOf(AuditRepository::class, $o->getRepository());
        $this->assertEquals($repository, $o->getRepository());
    }

    public function testGetSet(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $request,
            $userAgent,
            $authentication,
            $userRepository
        );

        $o->setDetail('foo')
          ->setEvent('create')
          ->setTags(['one', 'two']);

        $this->assertInstanceOf(AuditRepository::class, $o->getRepository());
        $this->assertEquals('foo', $this->getPropertyValue(Auditor::class, $o, 'detail'));
        $this->assertEquals('create', $this->getPropertyValue(Auditor::class, $o, 'event'));
        $this->assertCount(2, $this->getPropertyValue(Auditor::class, $o, 'tags'));
    }


    public function testSaveSuccess(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class, [
            'save' => true
        ]);
        $request = $this->getMockInstance(ServerRequest::class);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $request,
            $userAgent,
            $authentication,
            $userRepository
        );

        $o->setDetail('foo')
          ->setEvent('create')
          ->setTags(['one', 'two']);


        $result = $o->save();

        $this->assertTrue($result);
    }

    public function testSaveError(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class, [
            'save' => false
        ]);
        $request = $this->getMockInstance(ServerRequest::class);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $request,
            $userAgent,
            $authentication,
            $userRepository
        );

        $o->setDetail('foo')
          ->setEvent('create')
          ->setTags(['one', 'two']);


        $result = $o->save();

        $this->assertFalse($result);
    }
}

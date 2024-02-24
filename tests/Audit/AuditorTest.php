<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit;

use Platine\Container\Container;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Audit\Model\AuditRepository;
use Platine\Framework\Audit\SessionUser;
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

        $audtiUser = $this->getMockInstance(SessionUser::class);
        $repository = $this->getMockInstance(AuditRepository::class);
        $container = $this->getMockInstance(Container::class);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $container,
            $userAgent,
            $audtiUser,
            $userRepository
        );

        $this->assertInstanceOf(AuditRepository::class, $o->getRepository());
        $this->assertEquals($repository, $o->getRepository());
    }

    public function testGetSet(): void
    {

        $repository = $this->getMockInstance(AuditRepository::class);
        $container = $this->getMockInstance(Container::class);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $audtiUser = $this->getMockInstance(SessionUser::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $container,
            $userAgent,
            $audtiUser,
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
        $request = $this->getMockInstance(ServerRequest::class);

        $repository = $this->getMockInstance(AuditRepository::class, [
            'save' => true
        ]);

        $container = $this->getMockInstance(Container::class, [
            'get' => $request
        ]);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $audtiUser = $this->getMockInstance(SessionUser::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $container,
            $userAgent,
            $audtiUser,
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
        $request = $this->getMockInstance(ServerRequest::class);

        $repository = $this->getMockInstance(AuditRepository::class, [
            'save' => false
        ]);
        $container = $this->getMockInstance(Container::class, [
            'get' => $request
        ]);
        $userAgent = $this->getMockInstance(UserAgent::class);
        $audtiUser = $this->getMockInstance(SessionUser::class);
        $userRepository = $this->getMockInstance(UserRepository::class);

        $o = new Auditor(
            $repository,
            $container,
            $userAgent,
            $audtiUser,
            $userRepository
        );

        $o->setDetail('foo')
          ->setEvent('create')
          ->setTags(['one', 'two']);


        $result = $o->save();

        $this->assertFalse($result);
    }
}

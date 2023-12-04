<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Action;

use Exception;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Http\Action\CrudAction;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;
use Platine\Orm\Query\EntityQuery;
use Platine\Pagination\Pagination;
use Platine\Template\Template;
use Platine\Test\Framework\Fixture\MyCrudAction;
use Platine\Test\Framework\Fixture\MyEntity;
use Platine\Test\Framework\Fixture\MyLang;
use Platine\Test\Framework\Fixture\MyRepository;

class CrudActionTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $this->assertInstanceOf(CrudAction::class, $o);
    }

    public function testIndex(): void
    {
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'count' => 1
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'query' => $entityQuery,
        ]);

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->index($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testDetailNotFound(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => null,
        ]);

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->detail($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }

    public function testDetailSuccess(): void
    {
        $entity = $this->getMockInstance(MyEntity::class);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
        ]);

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->detail($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testCreateFirstPage(): void
    {
        $entity = $this->getMockInstance(MyEntity::class);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'GET',
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->create($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testCreateFormValidationFailed(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->create($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testCreateDuplicate(): void
    {
        $oldEntity = $this->getMockInstance(MyEntity::class);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'findBy' => $oldEntity
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
            'getParsedBody' => ['name' => 'foo', 'status' => 'bar'],
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->create($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'findBy' => null
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
            'getParsedBody' => ['name' => 'foo', 'status' => 'bar'],
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->create($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }

    public function testCreateError(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'findBy' => null
        ]);

        $repository->expects($this->exactly(1))
                   ->method('save')
                   ->will($this->throwException(new Exception()));

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
            'getParsedBody' => ['name' => 'foo', 'status' => 'bar'],
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->create($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testUpdateNotFound(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => null,
        ]);

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->update($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }

    public function testUpdateFirstPage(): void
    {
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'GET',
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->update($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testUpdateFormValidationFailed(): void
    {
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->update($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testUpdateDuplicate(): void
    {
        $oldEntity = $this->getMockInstance(MyEntity::class);
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
            'findBy' => $oldEntity,
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
            'getParsedBody' => ['name' => 'foo', 'status' => 'bar'],
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->update($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
            'findBy' => null,
        ]);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
            'getParsedBody' => ['name' => 'foo', 'status' => 'bar'],
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->update($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }

    public function testUpdateError(): void
    {
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
            'findBy' => null,
        ]);

        $repository->expects($this->exactly(1))
                   ->method('save')
                   ->will($this->throwException(new Exception()));

        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST',
            'getParsedBody' => ['name' => 'foo', 'status' => 'bar'],
        ]);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->update($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testDeleteNotFound(): void
    {
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => null,
        ]);

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->delete($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }

    public function testDeleteSuccess(): void
    {
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
        ]);

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->delete($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }

    public function testDeleteError(): void
    {
        $entity = $this->getMockInstance(MyEntity::class, [
            '__get' => 'foo'
        ]);
        $lang = $this->getMockInstance(MyLang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $template = $this->getMockInstance(Template::class);
        $flash = $this->getMockInstance(Flash::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $logger = $this->getMockInstance(Logger::class);
        $repository = $this->getMockInstance(MyRepository::class, [
            'find' => $entity,
        ]);

        $repository->expects($this->exactly(1))
                   ->method('delete')
                   ->will($this->throwException(new Exception()));

        $request = $this->getMockInstance(ServerRequest::class);

        $o = new MyCrudAction($lang, $pagination, $template, $flash, $routeHelper, $logger, $repository);
        $resp = $o->delete($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
    }
}

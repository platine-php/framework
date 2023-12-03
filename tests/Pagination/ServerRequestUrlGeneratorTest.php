<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Pagination;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Pagination\ServerRequestUrlGenerator;
use Platine\Http\ServerRequest;
use Platine\Http\Uri;

/*
 * @group core
 * @group framework
 */
class ServerRequestUrlGeneratorTest extends PlatineTestCase
{
    public function testGeneratePageUrlDefault(): void
    {
        $uri = $this->getMockInstance(Uri::class, [
            'getQuery' => ''
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri
        ]);
        $queryName = 'page';
        $o = new ServerRequestUrlGenerator($request, $queryName);
        $this->assertEquals('?page=1', $o->generatePageUrl(1));
    }

    public function testGeneratePageUrlWithExistingQueryString(): void
    {
        $uri = $this->getMockInstance(Uri::class, [
            'getQuery' => 'user=1'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri
        ]);
        $queryName = 'page';
        $o = new ServerRequestUrlGenerator($request, $queryName);
        $this->assertEquals('?user=1&page=1', $o->generatePageUrl(1));
    }

    public function testGeneratePageUrlWithExistingPageQueryString(): void
    {
        $uri = $this->getMockInstance(Uri::class, [
            'getQuery' => 'page=15'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri
        ]);
        $queryName = 'page';
        $o = new ServerRequestUrlGenerator($request, $queryName);
        $this->assertEquals('?page=1', $o->generatePageUrl(1));
    }

    public function testGeneratePageUrlWithExistingPageAndQueryString(): void
    {
        $uri = $this->getMockInstance(Uri::class, [
            'getQuery' => 'user=10&page=15'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri
        ]);
        $queryName = 'page';
        $o = new ServerRequestUrlGenerator($request, $queryName);
        $this->assertEquals('?user=10&page=1', $o->generatePageUrl(1));
        $this->assertEquals('?user=10&page={num}', $o->getUrlPattern());
    }
}

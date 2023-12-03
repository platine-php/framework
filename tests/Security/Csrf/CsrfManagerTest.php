<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Csrf;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Framework\Security\Csrf\Storage\CsrfNullStorage;
use Platine\Http\ServerRequest;

/*
 * @group core
 * @group security
 */
class CsrfManagerTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $storage = new CsrfNullStorage();
        $config = $this->getMockInstance(Config::class);

        $o = new CsrfManager($config, $storage);

        $this->assertInstanceOf(CsrfManager::class, $o);
    }

    public function testGetToken(): void
    {
        global $mock_sha1_foo;

        $mock_sha1_foo = true;
        $storage = new CsrfNullStorage();
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $this->assertEquals('foo', $o->getToken());
    }

    public function testValidateStorageReturnNull(): void
    {
        $storage = new CsrfNullStorage();
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $request = $this->getMockInstance(ServerRequest::class);
        $this->assertFalse($o->validate($request));
    }

    public function testValidateTokenAlreadyExpired(): void
    {
        $storage = new CsrfNullStorage();
        $storage->set('csrf_key', 'bar', time() - 100);
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $request = $this->getMockInstance(ServerRequest::class);
        $this->assertFalse($o->validate($request));
    }

    public function testValidateRequestTokenNotFound(): void
    {
        $storage = new CsrfNullStorage();
        $storage->set('csrf_key', 'bar', time() + 1000);
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $request = $this->getMockInstance(ServerRequest::class);
        $this->assertFalse($o->validate($request));

        $o->clear();
        $this->assertFalse($o->validate($request));
    }

    public function testValidateRequestTokenNotMatch(): void
    {
        $storage = new CsrfNullStorage();
        $storage->set('csrf_key', 'bar', time() + 1000);
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => ['csrf_key' => 'foo']
        ]);
        $this->assertFalse($o->validate($request));
    }

    public function testValidate(): void
    {
        $storage = new CsrfNullStorage();
        $storage->set('csrf_key', 'bar', time() + 1000);
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => ['csrf_key' => 'bar']
        ]);
        $this->assertTrue($o->validate($request));
    }

    public function testValidateUsingRequestHeader(): void
    {
        $storage = new CsrfNullStorage();
        $storage->set('csrf_key', 'bar', time() + 1000);
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $request = $this->getMockInstance(ServerRequest::class, [
            'getHeaderLine' => 'bar'
        ]);
        $this->assertTrue($o->validate($request));
    }

    public function testGetTokenQuery(): void
    {
        $storage = new CsrfNullStorage();
        $storage->set('csrf_key', 'bar', time() + 1000);
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'key' => 'csrf_key',
                'expire' => 600,
            ]
        ]);

        $o = new CsrfManager($config, $storage);

        $queries = $o->getTokenQuery();
        $this->assertCount(1, $queries);
        $this->assertArrayHasKey('csrf_key', $queries);
        $this->assertEquals('bar', $queries['csrf_key']);
    }
}

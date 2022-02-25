<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Env;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Env\Env;

/*
 * @group core
 * @group framework
 */
class EnvTest extends PlatineTestCase
{
    public function testGetDefault(): void
    {
        $this->assertEquals(100, Env::get('not found env key', 100, 'int'));
    }

    public function testGetGetEnv(): void
    {
        global $mock_getenv_to_foo;
        $mock_getenv_to_foo = true;
        $this->assertEquals('foo', Env::get('getenv_key'));
    }

    public function testGetEnvSuperGlobal(): void
    {
        $_ENV['env_key'] = 'foo';
        $this->assertEquals('foo', Env::get('env_key'));
    }

    public function testGetServerSuperGlobal(): void
    {
        $_SERVER['server_key'] = 'foo';
        $this->assertEquals('foo', Env::get('server_key'));
    }

    public function testGetUsingResolved(): void
    {
        $_SERVER['server_key1'] = 'foo';
        $_SERVER['server_key2'] = '${server_key1}/bar';
        $this->assertEquals('foo', Env::get('server_key1'));
        $this->assertEquals('foo/bar', Env::get('server_key2'));
    }
}

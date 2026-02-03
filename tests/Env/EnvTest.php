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

    public function testGetUsingArray(): void
    {
        $_ENV['policies'] = 'foo;bar';
        $this->assertCount(0, Env::get('array_not_found', [], 'array'));
        $res = Env::get('policies', [], 'array', ['separator' => ';']);
        $this->assertCount(2, $res);
        $this->assertEquals('foo', $res[0]);
        $this->assertEquals('bar', $res[1]);

        $_ENV['policies'] = 'foo';
        $res1 = Env::get('policies', [], 'array');
        $this->assertCount(1, $res1);
        $this->assertEquals('foo', $res1[0]);
    }

    public function testGetUsingDuration(): void
    {
        $_ENV['default'] = '34';
        $_ENV['ms'] = '367090ms';
        $_ENV['s'] = '64s';
        $_ENV['m'] = '5m';
        $_ENV['h'] = '4h';
        $_ENV['w'] = '2w';
        $_ENV['unknow'] = '34i';

        $this->assertEquals(34, Env::get('default', null, 'duration'));
        $this->assertEquals(367, Env::get('ms', null, 'duration'));
        $this->assertEquals(64, Env::get('s', null, 'duration'));
        $this->assertEquals(300, Env::get('m', null, 'duration'));
        $this->assertEquals(14400, Env::get('h', null, 'duration'));
        $this->assertEquals(1209600, Env::get('w', null, 'duration'));
        $this->assertEquals(34, Env::get('unknow', null, 'duration'));
    }

    public function testGetUsingArgumentValue(): void
    {
        global $mock_preg_replace_callback_to_null;
        $mock_preg_replace_callback_to_null = true;
        $_SERVER['server_key1'] = 'foo';
        $_SERVER['server_key2'] = '${server_keyX}/bar';
        $this->assertEquals('foo', Env::get('server_key1'));
        $this->assertEquals('${server_keyX}/bar', Env::get('server_key2'));
    }
}

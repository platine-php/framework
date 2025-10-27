<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\RedisClient;
use RuntimeException;

class RedisClientTest extends PlatineTestCase
{
    public function testSocketConnectError(): void
    {
        global $mock_stream_socket_client_to_false;

        $mock_stream_socket_client_to_false = true;
        $o = new RedisClient();
        $this->expectException(RuntimeException::class);
        $o->ttl('foo');
    }

    public function testCannotReadFromSocket(): void
    {
        global $mock_stream_socket_client_to_value, $mock_fgets_to_false;

        $vfsRoot = vfsStream::setup();
        $file = $this->createVfsFile('resource.png', $vfsRoot, '');

        $mock_fgets_to_false = false;
        $mock_stream_socket_client_to_value = fopen($file->url(), '+w');
        $o = new RedisClient();
        $this->expectException(RuntimeException::class);
        $o->ttl('foo');
    }

    public function testRedisReturnAnError(): void
    {
        global $mock_stream_socket_client_to_value, $mock_fgets_to_value;

        $vfsRoot = vfsStream::setup();
        $file = $this->createVfsFile('resource.png', $vfsRoot, '(integer) -2');

        $mock_stream_socket_client_to_value = fopen($file->url(), '+w');
        $mock_fgets_to_value = '-2' . "\r\n";

        $o = new RedisClient();
        $this->expectException(RuntimeException::class);
        $o->set('key', 'bar');
    }

    public function testBulkReplyResultNull(): void
    {
        global $mock_stream_socket_client_to_value, $mock_fgets_to_value;

        $vfsRoot = vfsStream::setup();
        $file = $this->createVfsFile('resource.png', $vfsRoot, '');

        $mock_stream_socket_client_to_value = fopen($file->url(), '+w');
        $mock_fgets_to_value = '$-1' . "\r\n";

        $o = new RedisClient();
        $result = $o->set('key', 'bar');
        $this->assertNull($result);
    }

    public function testBulkReplyError(): void
    {
        global $mock_stream_socket_client_to_value, $mock_fread_to_false;

        $vfsRoot = vfsStream::setup();
        $file = $this->createVfsFile('resource.txt', $vfsRoot, "$4\r\n");
        $mock_fread_to_false = true;
        $mock_stream_socket_client_to_value = fopen($file->url(), 'r');

        $o = new RedisClient();
        $this->expectException(RuntimeException::class);
        $o->set('key', 'bar');
    }

    public function testMultiBulkReply(): void
    {
        global $mock_stream_socket_client_to_value;

        $vfsRoot = vfsStream::setup();
        $file = $this->createVfsFile('resource.txt', $vfsRoot, "*3\r\n$5\r\nhello\r\n$5\r\nworld\r\n$1\r\n!\r\n");

        $mock_stream_socket_client_to_value = fopen($file->url(), 'r');

        $o = new RedisClient();
        $result = $o->set('key', 'bar');

        $this->assertCount(3, $result);
        $this->assertEquals('hello', $result[0]);
        $this->assertEquals('world', $result[1]);
        $this->assertEquals('!', $result[2]);
    }

    public function testSuccess(): void
    {
        global $mock_stream_socket_client_to_value;

        $vfsRoot = vfsStream::setup();
        $file = $this->createVfsFile('resource.text', $vfsRoot, " OK\r\n");

        $mock_stream_socket_client_to_value = fopen($file->url(), 'r');

        $o = new RedisClient();
        $result = $o->set('key', 'bar');
        $this->assertEquals('OK', $result);
    }
}

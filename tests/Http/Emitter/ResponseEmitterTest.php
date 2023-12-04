<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Emitter;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Emitter\Exception\HeadersAlreadySentException;
use Platine\Framework\Http\Emitter\Exception\OutputAlreadySentException;
use Platine\Framework\Http\Emitter\ResponseEmitter;
use Platine\Http\Response;
use Platine\Http\Stream;

class ResponseEmitterTest extends PlatineTestCase
{
    protected $vfsRoot;
    protected $vfsPath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('my_tests')->at($this->vfsRoot);
    }

    public function testConstructorDefault(): void
    {
        $o = new ResponseEmitter(null);
        $this->assertNull($this->getPropertyValue(ResponseEmitter::class, $o, 'bufferLength'));
    }

    public function testConstructorBufferLengthIsSet(): void
    {
        $o = new ResponseEmitter(10);
        $this->assertEquals(10, $this->getPropertyValue(ResponseEmitter::class, $o, 'bufferLength'));
    }

    public function testConstructorBufferLengthIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $o = new ResponseEmitter(-1);
    }

    public function testEmitHeadersAlreadySent(): void
    {
        global $mock_headers_sent_to_true;
        $mock_headers_sent_to_true = true;

        $response = $this->getMockInstance(Response::class);

        $o = new ResponseEmitter(null);
        $this->expectException(HeadersAlreadySentException::class);
        $o->emit($response, true);
    }
    public function testEmitOutputAlreadyPrint(): void
    {
        global $mock_headers_sent_to_false,
               $mock_ob_get_level_to_error,
               $mock_ob_get_length_to_error;

        $mock_headers_sent_to_false = true;
        $mock_ob_get_length_to_error = true;
        $mock_ob_get_level_to_error = true;

        $response = $this->getMockInstance(Response::class);

        $o = new ResponseEmitter(null);
        $this->expectException(OutputAlreadySentException::class);
        $o->emit($response, true);
    }

    public function testEmitWithBodyAll(): void
    {
        global $mock_headers_sent_to_false;
        $mock_headers_sent_to_false = true;

        $file = $this->createVfsFile('file.txt', $this->vfsPath, 'response body');

        $body = new Stream($file->url());
        $response = $this->getMockInstance(Response::class, [
            'getStatusCode' => 200,
            'getBody' => $body,
            'getHeaders' => [
                'name' => ['foo'],
                'Set-Cookie' => ['bar'],
            ],
        ]);

        $o = new ResponseEmitter(null);
        $this->expectOutputString('response body');
        $o->emit($response, true);
    }

    public function testEmitWithBodyChunck(): void
    {
        global $mock_headers_sent_to_false;
        $mock_headers_sent_to_false = true;

        $file = $this->createVfsFile('file.txt', $this->vfsPath, 'response body');

        $body = new Stream($file->url());
        $response = $this->getMockInstance(Response::class, [
            'getStatusCode' => 200,
            'getBody' => $body,
            'getHeaders' => [
                'name' => ['foo'],
                'Set-Cookie' => ['bar'],
            ],
        ]);

        $o = new ResponseEmitter(5);
        $this->expectOutputString('response body');
        $o->emit($response, true);
    }

    public function testEmitWithBodyChunckContentRange(): void
    {
        global $mock_headers_sent_to_false;
        $mock_headers_sent_to_false = true;

        $file = $this->createVfsFile('file.txt', $this->vfsPath, 'response body seekable');

        $body = new Stream($file->url());
        $response = $this->getMockInstance(Response::class, [
            'getStatusCode' => 200,
            'getBody' => $body,
            'getHeaderLine' => 'bytes 2-6/22',
            'getHeaders' => [
                'name' => ['foo'],
                'Set-Cookie' => ['bar'],
            ],
        ]);

        $o = new ResponseEmitter(3);
        $this->expectOutputString('spons');
        $o->emit($response, true);
    }
}

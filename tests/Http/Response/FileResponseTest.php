<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Response;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Response\FileResponse;

/*
 * @group core
 * @group framework
 */
class FileResponseTest extends PlatineTestCase
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

    public function testEmptyFileName(): void
    {
        global $mock_realpath_to_same_param;
        $mock_realpath_to_same_param = true;

        $file = $this->createVfsFile('file.txt', $this->vfsPath, 'file response body');
        $o = new FileResponse($file->url(), null);
        $this->assertEquals(200, $o->getStatusCode());
        $this->assertEquals('File Transfer', $o->getHeaderLine('Content-Description'));
        $this->assertEquals('attachment; filename="file.txt"', $o->getHeaderLine('Content-Disposition'));
        $this->assertEquals('text/plain', $o->getHeaderLine('Content-Type'));
        $this->assertEquals('0', $o->getHeaderLine('Expires'));
        $this->assertEquals('must-revalidate', $o->getHeaderLine('Cache-Control'));
        $this->assertEquals('18', $o->getHeaderLine('Content-Length'));
        $this->assertEquals('file response body', (string) $o->getBody());
    }

    public function testCustomFileName(): void
    {
        global $mock_realpath_to_same_param;
        $mock_realpath_to_same_param = true;

        $file = $this->createVfsFile('file.txt', $this->vfsPath, 'file response body');
        $o = new FileResponse($file->url(), 'foo.txt');
        $this->assertEquals(200, $o->getStatusCode());
        $this->assertEquals('File Transfer', $o->getHeaderLine('Content-Description'));
        $this->assertEquals('attachment; filename="foo.txt"', $o->getHeaderLine('Content-Disposition'));
        $this->assertEquals('text/plain', $o->getHeaderLine('Content-Type'));
        $this->assertEquals('0', $o->getHeaderLine('Expires'));
        $this->assertEquals('must-revalidate', $o->getHeaderLine('Cache-Control'));
        $this->assertEquals('18', $o->getHeaderLine('Content-Length'));
        $this->assertEquals('file response body', (string) $o->getBody());
    }
}

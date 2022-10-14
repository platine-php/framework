<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\CsvReader;

class CsvReaderTest extends PlatineTestCase
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

    public function testGetSet(): void
    {
        $o = new CsvReader();
        $this->assertEquals(';', $o->getDelimter());
        $this->assertEquals(0, $o->getLimit());
        $this->assertEquals(0, $o->count());
        $this->assertEmpty($o->getHeaders());
        $this->assertEmpty($o->all());
    }

    public function testSetLimit(): void
    {
        $o = new CsvReader();
        $this->assertEquals(0, $o->getLimit());
        $o->setLimit(100);
        $this->assertEquals(100, $o->getLimit());
    }


    public function testSetDelimiter(): void
    {
        $o = new CsvReader();
        $this->assertEquals(';', $o->getDelimter());
        $o->setDelimter(',');
        $this->assertEquals(',', $o->getDelimter());

        //Invalid format
        $this->expectException(InvalidArgumentException::class);
        $o->setDelimter('j');
    }

    public function testSetFile(): void
    {
        global $mock_file_exists_to_false;
        $mock_file_exists_to_false = true;

        $o = new CsvReader();
        $this->expectException(InvalidArgumentException::class);
        $o->setFile('j');
    }

    public function testParseCannotOpenFile(): void
    {
        global $mock_fopen_to_false;
        $mock_fopen_to_false = true;

        $o = new CsvReader();
        $file = $this->createVfsFile('my_file', $this->vfsPath, 'foo');
        $this->expectException(InvalidArgumentException::class);
        $o->setFile($file->url());
        $o->parse();
    }

    public function testParseSuccess(): void
    {
        $o = new CsvReader();
        $data = "last name;first name;age
        banabool;kitoko;9
        DOE;John;18";
        $file = $this->createVfsFile('my_file', $this->vfsPath, $data);
        $o->setFile($file->url());
        $o->parse();
        $this->assertEquals(2, $o->count());
        $this->assertCount(3, $o->getHeaders());
        $all = $o->all();
        $this->assertCount(2, $all);
        $this->assertEquals($all[0]['age'], 9);
        $this->assertEquals($all[1]['first name'], 'John');
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Env;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Env\Env;
use Platine\Framework\Env\Loader;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class LoaderTest extends PlatineTestCase
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

    public function testLoadFileNotFound(): void
    {
        $o = new Loader();
        $this->expectException(InvalidArgumentException::class);
        $o->load('file/that/does/not/exist');
    }

    public function testLoadInvalidFileContent(): void
    {
        global $mock_parse_ini_string_to_false;
        $mock_parse_ini_string_to_false = true;
        $o = new Loader();
        $file = $this->createVfsFile('.env', $this->vfsPath);
        $this->expectException(RuntimeException::class);
        $o->load($file->url());
    }

    /**
     * @dataProvider loadSuccessDataProvider
     * @param int $mode
     * @return void
     */
    public function testLoadSuccess(int $mode): void
    {
        $o = new Loader();
        $file = $this->createVfsFile('.env', $this->vfsPath, $this->getFileContent());
        $o->load($file->url(), false, $mode);
        $this->assertEquals(1, Env::get('int', null, 'int'));
        $this->assertEquals('my value', Env::get('string'));
        $this->assertTrue(Env::get('boolv'));
        $this->assertNull(Env::get('nullv'));
        $this->assertEmpty(Env::get('emptyv'));
        $this->assertEquals('bar/tmp', Env::get('depv'));
        $this->assertEquals(1, Env::get('int', null, 'footype')); //invalid type
    }


    /**
     * Data provider for "testLoadSuccess"
     * @return array
     */
    public function loadSuccessDataProvider(): array
    {
        return [
            [Loader::ALL],
            [Loader::ENV],
            [Loader::SERVER],
            [Loader::PUTENV],
        ];
    }

    private function getFileContent(): string
    {
        return '
            int=1
            string="my value"
            boolv=true
            nullv=null
            foo=bar
            foo=bar
            emptyv=
            depv=${foo}/tmp
        ';
    }
}

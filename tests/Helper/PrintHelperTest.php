<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Cache\Cache;
use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Helper\FileHelper;
use Platine\Framework\Helper\PrintHelper;
use Platine\PDF\PDF;
use Platine\Template\Template;

class PrintHelperTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $pdf = $this->getMockInstance(PDF::class);
        $template = $this->getMockInstance(Template::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $config = $this->getMockInstance(Config::class);
        $cache = $this->getMockInstance(Cache::class);
        $fileHelper = $this->getMockInstance(FileHelper::class);
        $o = new PrintHelper($pdf, $template, $config, $filesystem, $cache, $fileHelper);

        $this->assertInstanceOf(PrintHelper::class, $o);
    }

    public function testGenerateReport(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'getPath' => 'report_file',
        ]);

        $pdf = $this->getMockInstance(PDF::class);
        $template = $this->getMockInstance(Template::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_print_path', '', 'print_path'],
                ['platform.report_debug_path', null, 'debug_path'],
                ['platform.cache_print_report_content', false, true],
            ]
        ]);
        $cache = $this->getMockInstance(Cache::class, [
            'get' => 'cache content',
        ]);
        $fileHelper = $this->getMockInstance(FileHelper::class, [
            'getRootPath' => 'print_path',
        ]);
        $o = new PrintHelper($pdf, $template, $config, $filesystem, $cache, $fileHelper);
        $this->expectMethodCallCount($pdf, 'setContent');
        $this->expectMethodCallCount($pdf, 'save');

        $res = $o->generateReport('mail_user');

        $this->assertEquals($res, 'report_file');
    }

    public function testGenerateReportFileDoesNotExists(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'getPath' => 'report_file',
        ]);

        $pdf = $this->getMockInstance(PDF::class);
        $template = $this->getMockInstance(Template::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'get' => $file,
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_print_path', '', 'print_path'],
                ['platform.report_debug_path', null, 'debug_path'],
                ['platform.cache_print_report_content', false, true],
            ]
        ]);
        $cache = $this->getMockInstance(Cache::class, [
            'get' => 'cache content',
        ]);
        $fileHelper = $this->getMockInstance(FileHelper::class, [
            'getRootPath' => 'print_path',
        ]);
        $o = new PrintHelper($pdf, $template, $config, $filesystem, $cache, $fileHelper);
        $this->expectMethodCallCount($pdf, 'setContent');
        $this->expectMethodCallCount($pdf, 'save');

        $res = $o->generateReport('mail_user');

        $this->assertNull($res);
    }

    public function testPrintReport(): void
    {
        $pdf = $this->getMockInstance(PDF::class);
        $template = $this->getMockInstance(Template::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['platform.data_print_path', '', 'print_path'],
                ['platform.report_debug_path', null, 'debug_path'],
                ['platform.cache_print_report_content', false, false],
            ]
        ]);
        $cache = $this->getMockInstance(Cache::class, [
            'get' => 'cache content',
        ]);
        $fileHelper = $this->getMockInstance(FileHelper::class, [
            'getRootPath' => 'print_path',
        ]);
        $o = new PrintHelper($pdf, $template, $config, $filesystem, $cache, $fileHelper);
        $this->expectMethodCallCount($pdf, 'setContent');
        $this->expectMethodCallCount($pdf, 'download');

        $res = $o->printReport('mail_user');

        $this->assertNull($res);
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use org\bovigo\vfs\vfsStream;
use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\MailHelper;
use Platine\Framework\Helper\PrintHelper;
use Platine\Mail\Transport\NullTransport;
use Platine\Template\Template;

class MailHelperTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $transport = $this->getMockInstance(NullTransport::class);
        $printHelper = $this->getMockInstance(PrintHelper::class);
        $config = $this->getMockInstance(Config::class);
        $template = $this->getMockInstance(Template::class);
        $o = new MailHelper($template, $transport, $printHelper, $config);

        $this->assertInstanceOf(MailHelper::class, $o);
    }

    public function testSendReportMail(): void
    {
        $vfsRoot = vfsStream::setup();
        $file1 = $this->createVfsFile('file1.txt', $vfsRoot, 'file1');
        $file2 = $this->createVfsFile('file2.txt', $vfsRoot, 'file2');

        $transport = $this->getMockInstance(NullTransport::class, ['send' => true]);
        $printHelper = $this->getMockInstance(PrintHelper::class);
        $config = $this->getMockInstance(Config::class);
        $template = $this->getMockInstance(Template::class);
        $o = new MailHelper($template, $transport, $printHelper, $config);

        $this->expectMethodCallCount($printHelper, 'getReportContent');
        $this->expectMethodCallCount($printHelper, 'debugReport');
        $this->expectMethodCallCount($config, 'get');
        $this->expectMethodCallCount($template, 'renderString');

        $res = $o->sendReportMail(
            1,
            'Foo object',
            'myemail@example.com',
            ['name' => 'Foo'],
            [$file1->url(), 'file' => $file2->url()]
        );
        $this->assertTrue($res);
    }

    public function testSendReportMailEmptyReceiverAddress(): void
    {
        $transport = $this->getMockInstance(NullTransport::class);
        $printHelper = $this->getMockInstance(PrintHelper::class);
        $config = $this->getMockInstance(Config::class);
        $template = $this->getMockInstance(Template::class);
        $o = new MailHelper($template, $transport, $printHelper, $config);

        $res = $o->sendReportMail(
            1,
            'Foo object',
            ''
        );
        $this->assertFalse($res);
    }

    public function testSendReportMailTransportFailed(): void
    {
        $transport = $this->getMockInstance(NullTransport::class, ['send' => false]);
        $printHelper = $this->getMockInstance(PrintHelper::class);
        $config = $this->getMockInstance(Config::class);
        $template = $this->getMockInstance(Template::class);
        $o = new MailHelper($template, $transport, $printHelper, $config);

        $res = $o->sendReportMail(
            1,
            'Foo object',
            'foo@example.com'
        );
        $this->assertFalse($res);
    }
}

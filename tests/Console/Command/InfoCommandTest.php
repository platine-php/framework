<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Application;
use Platine\Console\IO\Interactor;
use Platine\Framework\App\Application as PlatineApplication;
use Platine\Framework\Console\Command\InfoCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class InfoCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $this->createVfsFile('composer.lock', $this->vfsPath, '{"packages": [
        {
            "name": "dompdf/dompdf",
            "version": "v3.1.4",
            "type": "library",
            "description": "DOMPDF is a CSS 2.1 compliant HTML to PDF converter",
            "homepage": "https://github.com/dompdf/dompdf",
            "time": "2025-10-29T12:43:30+00:00"
        }]}');

        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $app = $this->getMockInstance(Application::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['app', [], [
                    'name' => 'Foo',
                    'debug' => false
                ]],
            ]
        ]);
        $application = $this->getMockInstance(PlatineApplication::class, [
            'getRootPath' => $this->vfsPath->url(),
            'version' => '2.0.0-dev',
            'getEnvironment' => 'staging',
        ]);

        $o = new InfoCommand($config, $application);
        $o->bind($app);
        $o->parse(['platine', 'info']);
        $this->assertEquals('info', $o->getName());
        $o->execute();
        $this->assertStringContainsString('staging', $this->getConsoleOutputContent());
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console;

use org\bovigo\vfs\vfsStream;
use Platine\Config\Config;
use Platine\Console\Application;
use Platine\Console\Input\Reader;
use Platine\Console\IO\Interactor;
use Platine\Console\Output\Color;
use Platine\Console\Output\Writer;
use Platine\Dev\PlatineTestCase;

class BaseCommandTestCase extends PlatineTestCase
{
    protected $vfsRoot;
    protected $vfsPath;
    protected $vfsInputStream;
    protected $vfsOutputStream;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('my_tests')->at($this->vfsRoot);
        $this->vfsInputStream = $this->createVfsFileOnly('stdin', $this->vfsPath);
        $this->vfsOutputStream = $this->createVfsFileOnly('stdout', $this->vfsPath);
    }

    /**
     * Return writer instance for test
     * @return Writer
     */
    protected function getWriterInstance(): Writer
    {
        $color = new Color();
        $this->setPropertyValue(Color::class, $color, 'format', ':txt:');
        $writer = new Writer($this->vfsOutputStream->url(), $color);

        return $writer;
    }

    /**
     * Return reader instance for test
     * @return Reader
     */
    protected function getReaderInstance(): Reader
    {
        $reader = new Reader($this->vfsInputStream->url());


        return $reader;
    }

    /**
     * Return test output stream content
     * @return string
     */
    protected function getConsoleOutputContent(): string
    {
        return $this->vfsOutputStream->getContent();
    }

    /**
     * Write to test input stream
     * @return void
     */
    protected function createInputContent(string $text): void
    {
        file_put_contents($this->vfsInputStream->url(), $text, FILE_APPEND);
    }

    protected function getConsoleApp(array $configValues = []): array
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                $configValues
            ]
        ]);

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(
            Interactor::class,
            [
                'writer' => $writer,
                'reader' => $reader
            ],
            [
                'prompt',
                'confirm',
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(Application::class, [
            'io' => $interactor
        ]);


        return [$consoleApp, $reader, $writer, $config];
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console;

use org\bovigo\vfs\vfsStream;
use Platine\Console\Output\Color;
use Platine\Console\Output\Writer;
use Platine\Dev\PlatineTestCase;

class BaseCommandTestCase extends PlatineTestCase
{

    protected $vfsRoot;
    protected $vfsPath;
    protected $vfsOutputStream;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('my_tests')->at($this->vfsRoot);
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
     * Return test output stream content
     * @return string
     */
    protected function getConsoleOutputContent(): string
    {
        return $this->vfsOutputStream->getContent();
    }
}

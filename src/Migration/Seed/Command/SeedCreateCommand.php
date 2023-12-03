<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file SeedCreateCommand.php
 *
 *  The seed generation command class
 *
 *  @package    Platine\Framework\Migration\Seed\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Seed\Command\AbstractSeedCommand;
use Platine\Stdlib\Helper\Str;

/**
 * @class SeedCreateCommand
 * @package Platine\Framework\Migration\Seed\Command
 * @template T
 * @extends AbstractSeedCommand<T>
 */
class SeedCreateCommand extends AbstractSeedCommand
{
    /**
     * The seed name
     * @var string
     */
    protected string $name = '';

    /**
     * Create new instance
     * {@inheritdoc}
     */
    public function __construct(
        Application $app,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct($app, $config, $filesystem);
        $this->setName('seed:create')
             ->setDescription('Create a new seed');

        $this->addArgument('name', 'name of seed', null, false, true);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $writer = $this->io()->writer();

        $className = $this->getSeedClassName($this->name);
        $filename = $this->getFilenameFromClass($className);
        $fullPath = $this->seedPath . $filename;

        $writer->boldGreen('Seed detail: ')->eol();
        $writer->bold('Name: ');
        $writer->boldBlueBgBlack($this->name, true);
        $writer->bold('Class name: ');
        $writer->boldBlueBgBlack($className, true);
        $writer->bold('Filename: ');
        $writer->boldBlueBgBlack($filename, true);
        $writer->bold('Path: ');
        $writer->boldBlueBgBlack($fullPath, true)->eol();


        $io = $this->io();

        if ($io->confirm('Are you confirm the generation of new seed?', 'n')) {
            $this->checkSeedPath();
            $this->generateClass($fullPath, $className);
            $writer->boldGreen(sprintf(
                'Seed [%s] generated successfully',
                $this->name
            ))->eol();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        $writer->boldYellow('SEED GENERATION', true)->eol();

        $name = $this->getArgumentValue('name');
        if (!$name) {
            $io = $this->io();
            $name = $io->prompt('Enter the name of the seed', 'Seed description');
        }
        $this->name = $name;
    }

    /**
     * Generate the migration class
     * @param string $path
     * @param string $className
     * @return void
     */
    protected function generateClass(string $path, string $className): void
    {
        $template = $this->getTemplateClass();
        $content = Str::replaceFirst('%classname%', $className, $template);

        $file = $this->filesystem->file($path);
        $file->write($content);
    }

    /**
     * Return the seed template class
     * @return string
     */
    private function getTemplateClass(): string
    {
        return <<<EOF
        <?php
        declare(strict_types=1);
        
        namespace Platine\Framework\Migration\Seed;

        use Platine\Framework\Migration\Seed\AbstractSeed;

        class %classname% extends AbstractSeed
        {

            public function run(): void
            {
              //Action when run the seed

            }
        }
        EOF;
    }
}

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
 *  @file MigrationCreateCommand.php
 *
 *  The migration generation command class
 *
 *  @package    Platine\Framework\Migration\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Stdlib\Helper\Str;

/**
 * @class MigrationCreateCommand
 * @package Platine\Framework\Migration\Command
 * @template T
 * @extends AbstractCommand<T>
 */
class MigrationCreateCommand extends AbstractCommand
{
    /**
     * The migration name
     * @var string
     */
    protected string $name = '';

    /**
     * Create new instance
     * {@inheritdoc}
     */
    public function __construct(
        Application $app,
        MigrationRepository $repository,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct($app, $repository, $config, $filesystem);
        $this->setName('migration:create')
             ->setDescription('Create a new migration');

        $this->addArgument('name', 'name of migration', null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): mixed
    {
        $writer = $this->io()->writer();

        $version = date('Ymd_His');
        $className = $this->getMigrationClassName($this->name, $version);
        $filename = $this->getFilenameFromClass($className, $version);
        $fullPath = $this->migrationPath . $filename;

        $writer->boldGreen('Migration detail: ')->eol();
        $writer->bold('Name: ');
        $writer->boldBlueBgBlack($this->name, true);
        $writer->bold('Version: ');
        $writer->boldBlueBgBlack($version, true);
        $writer->bold('Class name: ');
        $writer->boldBlueBgBlack($className, true);
        $writer->bold('Filename: ');
        $writer->boldBlueBgBlack($filename, true);
        $writer->bold('Path: ');
        $writer->boldBlueBgBlack($fullPath, true)->eol();


        $io = $this->io();

        if ($io->confirm('Are you confirm the generation of new migration?', 'n')) {
            $this->checkMigrationPath();
            $this->generateClass($fullPath, $className);
            $writer->boldGreen(sprintf(
                'Migration [%s] generated successfully',
                $this->name
            ))->eol();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        $writer->boldYellow('MIGRATION GENERATION', true)->eol();

        $name = $this->getArgumentValue('name');
        if (empty($name)) {
            $io = $this->io();
            $name = $io->prompt('Enter the name of the migration', 'Migration description');
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
     * Return the migration template class
     * @return string
     */
    private function getTemplateClass(): string
    {
        return <<<EOF
        <?php
        declare(strict_types=1);
        
        namespace Platine\Framework\Migration;

        use Platine\Framework\Migration\AbstractMigration;

        class %classname% extends AbstractMigration
        {

            public function up(): void
            {
              //Action when migrate up

            }

            public function down(): void
            {
              //Action when migrate down

            }
        }
        EOF;
    }
}

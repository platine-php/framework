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
 *  @file SeedCreateDbCommand.php
 *
 *  The seed generation using an existing database data command class
 *
 *  @package    Platine\Framework\Migration\Seed\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Database\QueryBuilder;
use Platine\Database\Schema;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Seed\Command\AbstractSeedCommand;
use Platine\Stdlib\Helper\Str;

/**
 * @class SeedCreateDbCommand
 * @package Platine\Framework\Migration\Seed\Command
 * @template T
 * @extends AbstractSeedCommand<T>
 */
class SeedCreateDbCommand extends AbstractSeedCommand
{

    /**
     * The seed name
     * @var string
     */
    protected string $name = '';

    /**
     * The table name
     * @var string
     */
    protected string $table = '';

    /**
     * The schema to use
     * @var Schema
     */
    protected Schema $schema;

    /**
     * The instance of query builder
     * @var QueryBuilder
     */
    protected QueryBuilder $queryBuilder;

    /**
     * Create new instance
     * {@inheritodc}
     */
    public function __construct(
        Application $app,
        Config $config,
        Filesystem $filesystem,
        Schema $schema,
        QueryBuilder $queryBuilder
    ) {
        parent::__construct($app, $config, $filesystem);
        $this->schema = $schema;
        $this->queryBuilder = $queryBuilder;

        $this->setName('seed:createdb')
             ->setDescription('Create a new seed using existing data');

        $this->addArgument('table', 'name of the table', null, true, false);
        $this->addArgument('name', 'name of seed', null, false, true);
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $writer = $this->io()->writer();

        $this->table = $this->getArgumentValue('table');
        $className = $this->getSeedClassName($this->name);
        $filename = $this->getFilenameFromClass($className);
        $fullPath = $this->seedPath . $filename;

        $writer->boldGreen('Seed detail: ')->eol();
        $writer->bold('Name: ');
        $writer->boldBlueBgBlack($this->name, true);
        $writer->bold('Table : ');
        $writer->boldBlueBgBlack($this->table, true);
        $writer->bold('Class name: ');
        $writer->boldBlueBgBlack($className, true);
        $writer->bold('Filename: ');
        $writer->boldBlueBgBlack($filename, true);
        $writer->bold('Path: ');
        $writer->boldBlueBgBlack($fullPath, true)->eol();


        $io = $this->io();

        if ($io->confirm('Are you confirm the generation of new seed?', 'n')) {
            if (!$this->schema->hasTable($this->table, true)) {
                $writer->boldRed(sprintf(
                    'Database table [%s] does not exist',
                    $this->table
                ));
                return;
            }
            $this->checkSeedPath();
            $this->generateClass($fullPath, $className);
            $writer->boldGreen(sprintf(
                'Seed [%s] generated successfully',
                $this->name
            ))->eol();
        }
    }

    /**
     * {@inheritodc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        $writer->boldYellow('SEED GENERATION USING EXISTING DATA', true)->eol();

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
        $seedDefinition = $this->generateSeedFromTableData();
        $content = Str::replaceFirst('%classname%', $className, $template);
        $seedContent = Str::replaceFirst('%seed_content%', $seedDefinition, $content);

        $file = $this->filesystem->file($path);
        $file->write($seedContent);
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
              %seed_content%
            }
        }
        EOF;
    }

    /**
     * Generate the seed content using the current table data
     * @return string
     */
    private function generateSeedFromTableData(): string
    {
        $content = '';
        $data = $this->queryBuilder->from($this->table)
                    ->select()
                    ->fetchAssoc()
                    ->all();

        if (empty($data)) {
            return sprintf('// No data found on table "%s"', $this->table);
        }

        $content .= '
        $data = ' . var_export($data, true) . ';
        foreach ($data as $row) {
            $this->insert($row)->into(\'' . $this->table . '\');
        }
        ';

        return $content;
    }
}

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
 *  @file VendorPublishCommand.php
 *
 *  The Composer vendor publish command class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Stdlib\Helper\Composer;
use Platine\Stdlib\Helper\Json;
use Platine\Stdlib\Helper\Path;

/**
 * @class VendorPublishCommand
 * @package Platine\Framework\Console\Command
 * @template T
 */
class VendorPublishCommand extends Command
{

    /**
     * Application instance
     * @var Application
     */
    protected Application $application;

    /**
     * The vendor path
     * @var string
     */
    protected string $vendorPath;

    /**
     * The package installation path
     * @var string
     */
    protected string $packagePath = '';

    /**
     * The file system to use
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Package manifest information
     * @var array<string, mixed>
     */
    protected array $manifest = [];

    /**
     * The configuration to use
     * @var Config<T>
     */
    protected Config $config;

    /**
     * Create new instance
     * @param Application $app
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $app,
        Filesystem $filesystem,
        Config $config
    ) {
        parent::__construct(
            'vendor:publish',
            'Command to publish composer vendor configuration, migration, etc.'
        );
        $this->addArgument('name', 'The package name', null, true);
        $this->addOption('-o|--overwrite', 'Overwrite existing files.', false, false);
        $this->addOption('-c|--config', 'Publish only the configuration.', false, false);
        $this->addOption('-m|--migration', 'Publish only the migrations.', false, false);
        $this->addOption('-a|--all', 'Publish all files.', false, false);

        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->application = $app;
        $this->vendorPath = Path::normalizePathDS($app->getVendorPath(), true);
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $writer = $this->io()->writer();
        $name = $this->getArgumentValue('name');
        $writer->boldGreen(sprintf('PUBLISH OF PACKAGE [%s]', $name), true)->eol();

        $package = $this->getPackageInfo($name);
        if (empty($package)) {
            $writer->red(sprintf(
                'Can not find the composer package [%s].',
                $name
            ), true);
            return;
        }

        $packagePath = $this->vendorPath . $name;
        $packagePath = Path::convert2Absolute($packagePath);

        $this->packagePath = $packagePath;

        $writer->bold('Name: ');
        $writer->boldBlueBgBlack($package['name'], true);

        $writer->bold('Description: ');
        $writer->boldBlueBgBlack($package['description'], true);

        $writer->bold('Version: ');
        $writer->boldBlueBgBlack($package['version'], true);

        $writer->bold('Type: ');
        $writer->boldBlueBgBlack($package['type'], true);

        $writer->bold('Path: ');
        $writer->boldBlueBgBlack($packagePath, true)->eol();

        $extras = $package['extra'] ?? [];
        $manifest = $extras['platine'] ?? [];

        if (empty($manifest)) {
            $writer->boldGreen('NOTHING TO PUBLISH, COMMAND ENDED!', true);
            return;
        }

        $this->manifest = $manifest;

        $this->publishPackage();

        $writer->eol();
        $writer->boldGreen('Command finished successfully')->eol();
    }

    /**
     * Package publication
     * @return void
     */
    protected function publishPackage(): void
    {
        $all = $this->getOptionValue('all');
        if ($all) {
            $this->publishAll();
            return;
        }

        $config = $this->getOptionValue('config');
        if ($config) {
            $this->publishConfiguration();
        }

        $migration = $this->getOptionValue('migration');
        if ($migration) {
            $this->publishMigration();
        }
    }

    /**
     * Publish all assets for this package
     * @return void
     */
    protected function publishAll(): void
    {
        $this->publishConfiguration();
        $this->publishMigration();
    }

    /**
     * Publish the configuration
     * @return void
     */
    protected function publishConfiguration(): void
    {
        $manifest = $this->manifest;
        $config = $manifest['config'] ?? [];
        $destinationPath = Path::normalizePathDS(
            $this->application->getConfigPath(),
            true
        );
        foreach ($config as $cfg) {
            $this->publishItem($cfg, $destinationPath, 'configuration');
        }
    }

    /**
     * Publish the migration
     * @return void
     */
    protected function publishMigration(): void
    {
        $path = Path::convert2Absolute(
            $this->config->get('migration.path', 'migrations')
        );
        $destinationPath = Path::normalizePathDS($path, true);

        $manifest = $this->manifest;
        $migrations = $manifest['migration'] ?? [];
        foreach ($migrations as $migration) {
            $this->publishItem($migration, $destinationPath, 'migration');
        }
    }

    /**
     * Publish asset
     * @param string $src
     * @param string $dest
     * @param string $type
     * @return void
     */
    protected function publishItem(string $src, string $dest, string $type): void
    {
        $writer = $this->io()->writer();

        $writer->boldYellow(sprintf('Publish of package %s', $type), true);

        $sourceFilename = basename($src);
        $sourcePath = $this->packagePath . '/' . $src;
        $file = $this->filesystem->file($sourcePath);
        if (!$file->exists()) {
            $writer->red(sprintf(
                'Can not find the package file %s [%s].',
                $type,
                $file->getPath()
            ), true);
        } else {
            $destFile = $this->filesystem->file(
                $dest . $sourceFilename
            );

            $overwrite = $this->getOptionValue('overwrite');

            if ($destFile->exists() && !$overwrite) {
                $writer->red(sprintf(
                    "%s file \n[%s]\n already exist, if you want to overwrite"
                        . ' use option "--overwrite".',
                    $type,
                    $destFile->getPath()
                ), true);
            } else {
                $file->copyTo($dest);
                $writer->boldGreen(
                    sprintf(
                        'Package %s [%s] publish successfully',
                        $type,
                        $src,
                        $dest . $sourceFilename
                    ),
                    true
                );
            }
        }
    }

    /**
     * Return the information for given package
     * @param string $name
     * @return array<string, mixed>
     */
    protected function getPackageInfo(string $name): array
    {
        $packages = Composer::parseLockFile($this->application->getAppPath());
        foreach ($packages as $package) {
            $packageName = $package['name'] ?? '';
            if ($name === $packageName) {
                return $package;
            }
        }

        return [];
    }
}

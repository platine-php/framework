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

declare(strict_types=1);

namespace Platine\Framework\Job;

use Exception;
use Platine\Config\Config;
use Platine\Filesystem\DirectoryInterface;
use Platine\Filesystem\FileInterface;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Task\TaskInterface;
use Platine\Logger\LoggerInterface;

/**
* @class CleanTempFileTask
* @package Platine\Framework\Job
*/
class CleanTempFileTask implements TaskInterface
{
    /**
    * Create new instance
    * @param Filesystem $filesystem
    * @param Config $config
    * @param LoggerInterface $logger
    */
    public function __construct(
        protected Filesystem $filesystem,
        protected Config $config,
        protected LoggerInterface $logger
    ) {
    }

    /**
    * {@inheritdoc}
    */
    public function run(): void
    {
        $tmpPath = $this->config->get('platform.data_temp_path');
        $directory = $this->filesystem->directory($tmpPath);


        /** @var FileInterface[] $files */
        $files = $directory->read(DirectoryInterface::FILE);
        $days = 5; // TODO use configuration
        $time = time() - ($days * 86400);

        try {
            foreach ($files as $file) {
                if ($file->getMtime() <= $time) {
                    $file->delete();
                }
            }
        } catch (Exception $ex) {
            $this->logger->error('Error when execute task {task}, {error}', [
                'error' => $ex->getMessage(),
                'task' => $this->name(),
            ]);
        }
    }

        /**
    * {@inheritdoc}
    */
    public function expression(): string
    {
        return '0 3 * * *';
    }

    /**
    * {@inheritdoc}
    */
    public function name(): string
    {
        return 'clean temp files';
    }
}

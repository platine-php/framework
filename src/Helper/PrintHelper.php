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

namespace Platine\Framework\Helper;

use Platine\Cache\CacheInterface;
use Platine\Config\Config;
use Platine\Filesystem\FileInterface;
use Platine\Filesystem\Filesystem;
use Platine\PDF\PDF;
use Platine\Stdlib\Helper\Json;
use Platine\Template\Template;

/**
 * @class PrintHelper
 * @package Platine\Framework\Helper
 * @template T
 */
class PrintHelper
{
    /**
     * Create new instance
     * @param PDF $pdf
     * @param Template $template
     * @param Config<T> $config
     * @param Filesystem $filesystem
     * @param CacheInterface $cache
     * @param FileHelper $fileHelper
     */
    public function __construct(
        protected PDF $pdf,
        protected Template $template,
        protected Config $config,
        protected Filesystem $filesystem,
        protected CacheInterface $cache,
        protected FileHelper $fileHelper,
    ) {
    }

    /**
     * Generate the report
     * @param string|int $reportId
     * @param array<string, mixed> $data
     * @param string $filename
     * @param string $format
     * @return string|null the full file path
     */
    public function generateReport(
        string|int $reportId,
        array $data = [],
        string $filename = '',
        string $format = 'portrait'
    ): ?string {
        return $this->handleReport(
            $reportId,
            $data,
            $filename,
            true,
            $format
        );
    }

    /**
     * Print the report
     * @param string|int $reportId
     * @param array<string, mixed> $data
     * @param string $filename
     * @param string $format
     * @return void
     */
    public function printReport(
        string|int $reportId,
        array $data = [],
        string $filename = '',
        string $format = 'portrait'
    ): void {
        $this->handleReport(
            $reportId,
            $data,
            $filename,
            false,
            $format
        );
    }

    /**
     * Return the main information for all report
     * @return array<string, mixed>
     */
    public function getMainData(): array
    {
        $data = [];
        $data['current_time'] = date('Y-m-d H:i:s');
        $data['config'] = [
            'app' => $this->config->get('app'),
        ];

        return $data;
    }

    /**
     * Return the report content if available from cache
     * @param string|int $reportId
     * @return string
     */
    public function getReportContent(string|int $reportId): string
    {
        $useCache = $this->config->get('platform.cache_print_report_content', false);
        if ($useCache) {
            $cacheKey = sprintf('report_definition_%s', $reportId);
            $content = $this->cache->get($cacheKey);
            if ($content !== null) {
                return $content;
            }
        }


        return $this->getRealReportContent($reportId);
    }

    /**
     * Return the report content definition
     * @param string|int $reportId
     * @return string
     */
    public function getRealReportContent(string|int $reportId): string
    {
        return '';
    }

    /**
     * Debug report definition if enable
     * @param string|int $reportId
     * @param array<string, mixed> $reportData
     * @return void
     */
    public function debugReport(string|int $reportId, array $reportData): void
    {
        $reportDebugPath = $this->config->get('platform.report_debug_path');
        if ($reportDebugPath !== null) {
            $reportDebugFile = sprintf('%s/%s.log', $reportDebugPath, $reportId);
            $this->filesystem->file($reportDebugFile)
                             ->write(Json::encode(
                                 $reportData,
                                 JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                             ));
        }
    }

    /**
     * Handle generation of report
     * @param string|int $reportId
     * @param array<string, mixed> $data
     * @param string $filename
     * @param bool $save whether to save
     * @param string $format
     * @return string|null the full file path
     */
    protected function handleReport(
        string|int $reportId,
        array $data = [],
        string $filename = '',
        bool $save = false,
        string $format = 'portrait'
    ): ?string {
        $content = $this->getReportContent($reportId);

        if (empty($filename)) {
            $filename = uniqid('report_' . $reportId);
        }

        if (substr($filename, 0, -4) !== '.pdf') {
            $filename .= '.pdf';
        }

        // Add some main informations
        $mainInformations = $this->getMainData();
        $reportData = $data + $mainInformations;

        // If need debug
        $this->debugReport($reportId, $reportData);

        $html = $this->template->renderString($content, $reportData);
        $path = $this->fileHelper->getRootPath(
            $this->getSaveFilePath(),
            true
        );
        $filepath = sprintf('%s/%s', $path, $filename);

        $this->pdf->setContent($html)
                  ->setFilename($save ? $filepath : $filename)
                  ->setFormat($format)
                  ->generate();

        if ($save) {
            $this->pdf->save();
        } else {
            $this->pdf->download();
        }

        if ($save === false) {
            return null;
        }

        /** @var FileInterface|null $handle */
        $handle = $this->filesystem->get($filepath);

        if ($handle !== null && $handle->exists()) {
            return $handle->getPath();
        }

        return null;
    }

    /**
     * Return the save path of the generated report
     * @return string
     */
    protected function getSaveFilePath(): string
    {
        return $this->config->get('platform.data_print_path', '');
    }
}

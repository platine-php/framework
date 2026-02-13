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

use Platine\Config\Config;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Http\RouteHelper;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Pagination\Pagination;

/**
 * @class BaseActionHelper
 * @package Platine\Framework\Helper
 * @template T
 */
class BaseActionHelper
{
    /**
     * Create new instance
     * @param Pagination $pagination
     * @param ViewContext<T> $context
     * @param RouteHelper $routeHelper
     * @param Lang $lang
     * @param LoggerInterface $logger
     * @param Auditor $auditor
     * @param FileHelper<T> $fileHelper
     * @param Config<T> $config
     */
    public function __construct(
        protected Pagination $pagination,
        protected ViewContext $context,
        protected RouteHelper $routeHelper,
        protected Lang $lang,
        protected LoggerInterface $logger,
        protected Auditor $auditor,
        protected FileHelper $fileHelper,
        protected Config $config
    ) {
    }

    /**
     * Return the configuration instance
     * @return Config<T>
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Return the FileHelper
     * @return FileHelper<T>
     */
    public function getFileHelper(): FileHelper
    {
        return $this->fileHelper;
    }

    /**
     *
     * @return Lang
     */
    public function getLang(): Lang
    {
        return $this->lang;
    }

    /**
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     *
     * @return Auditor
     */
    public function getAuditor(): Auditor
    {
        return $this->auditor;
    }

        /**
     *
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     *
     * @return ViewContext<T>
     */
    public function getContext(): ViewContext
    {
        return $this->context;
    }

    /**
     *
     * @return RouteHelper
     */
    public function getRouteHelper(): RouteHelper
    {
        return $this->routeHelper;
    }
}

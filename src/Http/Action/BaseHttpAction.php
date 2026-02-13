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

namespace Platine\Framework\Http\Action;

use Platine\Config\Config;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Helper\BaseActionHelper;
use Platine\Framework\Helper\FileHelper;
use Platine\Framework\Helper\ViewContext;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Pagination\Pagination;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * @class BaseHttpAction
 * @package Platine\Framework\Http\Action
 * @template T
 */
abstract class BaseHttpAction implements RequestHandlerInterface
{
    /**
     * The filter list
     * @var array<string, mixed>
     */
    protected array $filters = [];

     /**
     * The filters name maps
     * @var array<string, string>
     */
    protected array $filterMaps = [];

    /**
     * The pagination limit
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * The pagination current page
     * @var int|null
     */
    protected ?int $page = null;

    /**
     * Whether to query all list without pagination
     * @var bool
     */
    protected bool $all = false;

    /**
     * The pagination instance
     * @var Pagination
     */
    protected Pagination $pagination;

    /**
     * The request to use
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The request data instance
     * @var RequestData
     */
    protected RequestData $param;

    /**
     * The view context
     * @var ViewContext<T>
     */
    protected ViewContext $context;

    /**
    * The RouteHelper instance
    * @var RouteHelper
    */
    protected RouteHelper $routeHelper;

    /**
    * The Lang instance
    * @var Lang
    */
    protected Lang $lang;

    /**
    * The LoggerInterface instance
    * @var LoggerInterface
    */
    protected LoggerInterface $logger;

    /**
     * The auditor instance
     * @var Auditor
     */
    protected Auditor $auditor;

    /**
     * The file helper instance
     * @var FileHelper<T>
     */
    protected FileHelper $fileHelper;

    /**
     * The application configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * Create new instance
     * @param BaseActionHelper<T> $actionHelper
     */
    public function __construct(BaseActionHelper $actionHelper)
    {
        $this->pagination = $actionHelper->getPagination();
        $this->context = $actionHelper->getContext();
        $this->routeHelper = $actionHelper->getRouteHelper();
        $this->lang = $actionHelper->getLang();
        $this->logger = $actionHelper->getLogger();
        $this->auditor = $actionHelper->getAuditor();
        $this->fileHelper = $actionHelper->getFileHelper();
        $this->config = $actionHelper->getConfig();
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->param = new RequestData($request);

        $this->setFilters();
        $this->setPagination();

        return $this->respond();
    }

    /**
     * Add view context
     * @param string $name
     * @param mixed $value
     * @return self<T>
     */
    public function addContext(string $name, mixed $value): self
    {
        $this->context->set($name, $value);

        return $this;
    }

    /**
     * Add context in one call
     * @param array<string, mixed> $data
     * @return self<T>
     */
    public function addContexts(array $data): self
    {
        foreach ($data as $name => $value) {
            $this->context->set($name, $value);
        }

        return $this;
    }

    /**
     * Return the response
     * @return ResponseInterface
     */
    abstract public function respond(): ResponseInterface;

    /**
     * Set filters information's
     * @return void
     */
    protected function setFilters(): void
    {
        $queries = $this->param->gets();
        //remove defaults
        unset(
            $queries['fields'],
            $queries['sort'],
            $queries['page'],
            $queries['limit'],
            $queries['all']
        );

        $this->filters = Arr::filterValue($queries);

        // Handle default filters
        $this->handleFilterDefault();

        // Handle dates filter's
        if (array_key_exists('start_date', $this->filters)) {
            $startDate = $this->filters['start_date'];
            // if no time is provided xxxx-xx-xx
            if (Str::length($startDate) === 10) {
                $startDate .= ' 00:00:00';
            }
            $this->filters['start_date'] = $startDate;
        }

        if (array_key_exists('end_date', $this->filters)) {
            $endDate = $this->filters['end_date'];
            // if no time is provided xxxx-xx-xx
            if (Str::length($endDate) === 10) {
                $endDate .= ' 23:59:59';
            }
            $this->filters['end_date'] = $endDate;
        }

        $ignoreDateFilters = $this->getIgnoreDateFilters();

        foreach ($ignoreDateFilters as $filterName) {
            if (array_key_exists($filterName, $this->filters)) {
                unset(
                    $this->filters['start_date'],
                    $this->filters['end_date']
                );
                break;
            }
        }
    }

    /**
     * Set the pagination information
     * @return void
     */
    protected function setPagination(): void
    {
        $param = $this->param;
        if ($param->get('all', null)) {
            $this->all = true;
            return;
        }

        $limit = $param->get('limit', null);
        if ($limit !== null) {
            $this->limit = (int) $limit;
        }

        $maxLimit = $this->config->get('pagination.max_limit', 1000);
        if ($this->limit !== null && $this->limit > $maxLimit) {
            $this->limit = $maxLimit;
        }

        $page = $param->get('page', null);
        if ($page) {
            $this->page = (int) $page;
        }

        if ($limit > 0 || $page > 0) {
            $this->all = false;
        }

        if ($this->limit > 0) {
            $this->pagination->setItemsPerPage($this->limit);
        }

        $currentPage = $this->page ?? 1;

        $this->pagination->setCurrentPage($currentPage);
    }

    /**
     * Handle filter default dates
     * @return void
     */
    protected function handleFilterDefault(): void
    {
    }

   /**
    * Ignore date filters if one of the given filters is present
    * @return array<string> $filters
    */
    protected function getIgnoreDateFilters(): array
    {
        return [];
    }
}

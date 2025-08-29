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
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Helper\FileHelper;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Helper\Sidebar;
use Platine\Framework\Helper\ViewContext;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Pagination\Pagination;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Template;

/**
 * @class BaseAction
 * @package Platine\Framework\Http\Action
 * @template T
 */
abstract class BaseAction implements RequestHandlerInterface
{
    /**
     * The field to use in query
     * @var string[]
     */
    protected array $fields = [];

    /**
     * The field columns maps
     * @var array<string, string>
     */
    protected array $fieldMaps = [];

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
     * The sort information's
     * @var array<string, string>
     */
    protected array $sorts = [];

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
     * The name of the view
     * @var string
     */
    protected string $viewName = '';

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
     * The Sidebar instance
     * @var Sidebar
     */
    protected Sidebar $sidebar;

    /**
     * The view context
     * @var ViewContext<T>
     */
    protected ViewContext $context;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
    * The RouteHelper instance
    * @var RouteHelper
    */
    protected RouteHelper $routeHelper;

    /**
    * The Flash instance
    * @var Flash
    */
    protected Flash $flash;

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
     * @param ActionHelper<T> $actionHelper
     */
    public function __construct(ActionHelper $actionHelper)
    {
        $this->pagination = $actionHelper->getPagination();
        $this->sidebar = $actionHelper->getSidebar();
        $this->context = $actionHelper->getContext();
        $this->template = $actionHelper->getTemplate();
        $this->routeHelper = $actionHelper->getRouteHelper();
        $this->flash = $actionHelper->getFlash();
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

        $this->setFields();
        $this->setFilters();
        $this->setSorts();
        $this->setPagination();

        return $this->respond();
    }

    /**
     * Set the view name
     * @param string $name
     * @return self<T>
     */
    public function setView(string $name): self
    {
        $this->viewName = $name;

        return $this;
    }

    /**
     * Add sidebar
     * @inheritDoc
     * @see Sidebar
     * @param array<string, mixed> $params
     * @param array<string, mixed> $extras
     * @return self<T>
     */
    public function addSidebar(
        string $group,
        string $title,
        string $name,
        array $params = [],
        array $extras = []
    ): self {
        $this->sidebar->add($group, $title, $name, $params, $extras);

        return $this;
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
     * Return the template response
     * @return TemplateResponse
     */
    protected function viewResponse(): TemplateResponse
    {
        $sidebarContent = $this->sidebar->render();
        if (!empty($sidebarContent)) {
            $this->addContext('sidebar', $sidebarContent);
        }
        $this->addContext('pagination', $this->pagination->render());
        $this->addContext('app_url', $this->config->get('app.url'));
        $this->addContext('request_method', $this->request->getMethod());

        // Application info
        $this->addContext('app_name', $this->config->get('app.name'));
        $this->addContext('app_version', $this->config->get('app.version'));

        // Used in the footer
        $this->addContext('current_year', date('Y'));

        // Maintenance status
        $this->addContext('maintenance_state', app()->isInMaintenance());

        // Set nonce for Content Security Policy
        $nonces  = $this->request->getAttribute(SecurityPolicy::class);

        if ($nonces !== null) {
            $this->addContext('style_nonce', $nonces['nonces']['style']);
            $this->addContext('script_nonce', $nonces['nonces']['script']);
        }

        // get CSRF token if exist
        $csrfToken = $this->request->getAttribute('csrf_token');
        if ($csrfToken !== null) {
            $this->addContext('csrf_token', $csrfToken);
        }

        return new TemplateResponse(
            $this->template,
            $this->viewName,
            $this->context->all()
        );
    }

    /**
     * Redirect the user to another route
     * @param string $route
     * @param array<string, mixed> $params
     * @param array<string, mixed> $queries
     * @return RedirectResponse
     */
    protected function redirect(
        string $route,
        array $params = [],
        array $queries = []
    ): RedirectResponse {
        $queriesStr = null;
        if (count($queries) > 0) {
            $queriesStr = Arr::query($queries);
        }

        $routeUrl = $this->routeHelper->generateUrl($route, $params);
        if ($queriesStr !== null) {
            $routeUrl .= '?' . $queriesStr;
        }

        return new RedirectResponse($routeUrl);
    }

    /**
     * Return the response
     * @return ResponseInterface
     */
    abstract public function respond(): ResponseInterface;

    /**
     * Set field information's
     * @return void
     */
    protected function setFields(): void
    {
        $fieldParams = $this->param->get('fields', '');
        if (!empty($fieldParams)) {
            $fields = explode(',', $fieldParams);
            $columns = [];
            foreach ($fields as $field) {
                $columns[] = $this->fieldMaps[$field] ?? $field;
            }
            $this->fields = $columns;
        }
    }

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

        $filterParams = $queries;
        if (count($filterParams) > 0) {
            $filters = [];
            foreach ($filterParams as $key => $value) {
                $name = $this->filterMaps[$key] ?? $key;
                if (is_string($value) && Str::length($value) > 0) {
                    $filters[$name] = $value;
                    continue;
                }

                if (is_array($value) && count($value) > 1) {
                    $filters[$name] = $value;
                }
            }

            $this->filters = $filters;
        }

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
     * Set sort information's
     * @return void
     */
    protected function setSorts(): void
    {
        $sortParams = $this->param->get('sort', '');
        if (!empty($sortParams)) {
            $sorts = explode(',', $sortParams);
            $columns = [];
            foreach ($sorts as $sort) {
                $order = 'ASC';
                $parts = explode(':', $sort);
                if (isset($parts[1]) && strtolower($parts[1]) === 'desc') {
                    $order = 'DESC';
                }

                $column = $this->fieldMaps[$parts[0]] ?? $parts[0];
                $columns[$column] = $order;
            }
            $this->sorts = $columns;
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

        if ($this->limit !== null && $this->limit > 100) {
            $this->limit = 100;
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
     * Parse the error message to handle delete or update of parent record
     * @param string $error
     * @return string
     */
    protected function parseForeignConstraintErrorMessage(string $error): string
    {
        /** MySQL **
         * SQLSTATE[23000]: Integrity constraint violation: 1217 Cannot delete or update a
         * parent row: a foreign key constraint fails [DELETE FROM `TABLE_NAME` WHERE `id` = XX]
         */

        /** MariaDB *
         * SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a
         * parent row: a foreign key constraint fails
         * ("DB_NAME"."TABLE_NAME", CONSTRAINT "basetable_fk_person_id" FOREIGN KEY ("person_id")
         * REFERENCES "persons" ("id") ON DELETE NO ACTION) [DELETE FROM `persons` WHERE `id` = XX]
         */
        $result = '';
        if (strpos($error, 'Cannot delete or update a parent row') !== false) {
            if (strpos($error, 'Integrity constraint violation: 1217') !== false) {
                // MySQL
                $result = $this->lang->tr('This record is related to another one');
            } else {
                // MariaDB
                $arr = explode('.', $error);
                $tmp = explode(',', $arr[1] ?? '');
                $result = $this->lang->tr('This record is related to another one [%s]', str_replace('_', ' ', $tmp[0]));
            }
        }

        return $result;
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

    /**
     * Redirect back to origin if user want to create new entity from
     * detail page
     * @return ResponseInterface|null
     */
    protected function redirectBackToOrigin(): ?ResponseInterface
    {
        $param = $this->param;
        $originId = (int) $param->get('origin_id', 0);
        $originRoute = $param->get('origin_route');

        if ($originRoute === null) {
            return null;
        }

        if ($originId === 0) {
            return $this->redirect($originRoute);
        }

        return $this->redirect($originRoute, ['id' => $originId]);
    }

    // REST API Part
    /**
     * Return the rest response
     * @param array<string, mixed>|object|mixed $data
     * @param int $statusCode
     * @param int $code
     *
     * @return ResponseInterface
     */
    protected function restResponse(
        $data = [],
        int $statusCode = 200,
        int $code = 0
    ): ResponseInterface {
        $extras = $this->context->all();
        if ($this->pagination->getTotalItems() > 0) {
            $extras['pagination'] = $this->pagination->getInfo();
        }

        return new RestResponse(
            $data,
            $extras,
            true,
            $code,
            '',
            $statusCode
        );
    }

    /**
     * Return the rest error response
     * @param string $message
     * @param int $statusCode
     * @param int $code
     * @param array<string, mixed> $extras
     *
     * @return ResponseInterface
     */
    protected function restErrorResponse(
        string $message,
        int $statusCode = 401,
        int $code = 4000,
        array $extras = []
    ): ResponseInterface {
        return new RestResponse(
            [],
            $extras,
            false,
            $code,
            $message,
            $statusCode
        );
    }

    /**
     * Return the rest server error response
     * @param string $message
     * @param int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function restServerErrorResponse(
        string $message = '',
        int $code = 5000
    ): ResponseInterface {
        return $this->restErrorResponse(
            $message,
            500,
            $code,
            []
        );
    }

    /**
     * Return the rest bad request error response
     * @param string $message
     * @param int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function restBadRequestErrorResponse(
        string $message = '',
        int $code = 4000
    ): ResponseInterface {
        return $this->restErrorResponse(
            $message,
            400,
            $code,
            []
        );
    }

    /**
     * Return the rest duplicate resource error response
     * @param string $message
     * @param int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function restConflictErrorResponse(
        string $message = '',
        int $code = 4090
    ): ResponseInterface {
        return $this->restErrorResponse(
            $message,
            409,
            $code,
            []
        );
    }

    /**
     * Return the rest not found error response
     * @param string $message
     * @param int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function restNotFoundErrorResponse(
        string $message = '',
        int $code = 4040
    ): ResponseInterface {
        return $this->restErrorResponse(
            $message,
            404,
            $code,
            []
        );
    }

    /**
     * Return the rest form validation error response
     * @param array<string, string> $errors
     * @param int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function restFormValidationErrorResponse(
        array $errors = [],
        int $code = 4220
    ): ResponseInterface {
        return $this->restErrorResponse(
            $this->lang->tr('Invalid Request Parameter(s)'),
            422,
            $code,
            ['errors' => $errors]
        );
    }
}

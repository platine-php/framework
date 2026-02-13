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

use Platine\Framework\Helper\BaseActionHelper;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Orm\Query\EntityQuery;
use Platine\Orm\RepositoryInterface;

/**
 * @class RestBaseAction
 * @package Platine\Framework\Http\Action
 * @template T
 */
abstract class RestBaseAction extends BaseHttpAction
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
     * The sort information's
     * @var array<string, string>
     */
    protected array $sorts = [];

    /**
     * Create new instance
     * @param BaseActionHelper<T> $actionHelper
     */
    public function __construct(BaseActionHelper $actionHelper)
    {
        parent::__construct($actionHelper);
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
     * Process pagination and sort
     * @param RepositoryInterface $repository
     * @param EntityQuery $query
     * @param string|array<string> $sortFields
     * @param string $sortDir
     * @return void
     */
    protected function handleRestPagination(
        RepositoryInterface $repository,
        EntityQuery $query,
        string|array $sortFields = 'name',
        string $sortDir = 'ASC'
    ): void {
        if ($this->all === false) {
            $totalItems = $repository->filters($this->filters)
                                     ->query()
                                     ->count('id');

            $currentPage = (int) $this->param->get('page', 1);

            $this->pagination->setTotalItems($totalItems)
                             ->setCurrentPage($currentPage);

            $limit = $this->pagination->getItemsPerPage();
            $offset = $this->pagination->getOffset();

            $query = $query->limit($limit)
                           ->offset($offset);
        }

        if (count($this->sorts) > 0) {
            foreach ($this->sorts as $column => $order) {
                $query = $query->orderBy($column, $order);
            }
        } else {
            $query = $query->orderBy($sortFields, $sortDir);
        }
    }

    /**
     * Return the REST response
     * @param mixed $data
     * @param int $statusCode
     * @param string|int $code the custom code
     *
     * @return ResponseInterface
     */
    protected function restResponse(
        mixed $data = [],
        int $statusCode = 200,
        string|int $code = 'OK'
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
     * Return the REST error response
     * @param string $message
     * @param int $statusCode
     * @param string|int $code
     * @param array<string, mixed> $extras
     *
     * @return ResponseInterface
     */
    protected function errorResponse(
        string $message,
        int $statusCode = 401,
        string|int $code = 'ERROR',
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
     * Return the REST created response
     * @param array<string, mixed>|object|mixed $data
     * @param string|int $code the custom code
     *
     * @return ResponseInterface
     */
    protected function createdResponse(
        mixed $data = [],
        string|int $code = 'CREATED'
    ): ResponseInterface {
        return $this->restResponse(
            $data,
            201,
            $code
        );
    }

    /**
     * Return the REST no content response
     * @param string|int $code the custom code
     *
     * @return ResponseInterface
     */
    protected function noContentResponse(
        string|int $code = 'NO_CONTENT'
    ): ResponseInterface {
        return $this->restResponse(
            [],
            204,
            $code
        );
    }

    /**
     * Return the REST bad request response
     * @param string $message
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function badRequestResponse(
        string $message = '',
        string|int $code = 'BAD_REQUEST'
    ): ResponseInterface {
        return $this->errorResponse(
            $message,
            400,
            $code,
            []
        );
    }

    /**
     * Return the REST unauthorized response
     * @param string $message
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function unauthorizedResponse(
        string $message = '',
        string|int $code = 'UNAUTHORIZED_ACCESS'
    ): ResponseInterface {
        return $this->errorResponse(
            $message,
            401,
            $code,
            []
        );
    }

    /**
     * Return the REST forbidden response
     * @param string $message
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function forbiddenResponse(
        string $message = '',
        string|int $code = 'FORBIDDEN'
    ): ResponseInterface {
        return $this->errorResponse(
            $message,
            403,
            $code,
            []
        );
    }

    /**
     * Return the REST not found response
     * @param string $message
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function notFoundResponse(
        string $message = '',
        string|int $code = 'RESOURCE_NOT_FOUND'
    ): ResponseInterface {
        return $this->errorResponse(
            $message,
            404,
            $code,
            []
        );
    }


    /**
     * Return the REST duplicate resource response
     * @param string $message
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function conflictResponse(
        string $message = '',
        string|int $code = 'DUPLICATE_RESOURCE'
    ): ResponseInterface {
        return $this->errorResponse(
            $message,
            409,
            $code,
            []
        );
    }

    /**
     * Return the REST form validation response
     * @param array<string, string> $errors
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function formValidationResponse(
        array $errors = [],
        string|int $code = 'INVALID_INPUT'
    ): ResponseInterface {
        return $this->errorResponse(
            $this->lang->tr('Invalid Request Parameter(s)'),
            422,
            $code,
            ['errors' => $errors]
        );
    }

    /**
     * Return the REST internal server error response
     * @param string $message
     * @param string|int $code the custom error code
     *
     * @return ResponseInterface
     */
    protected function internalServerErrorResponse(
        string $message = '',
        string|int $code = 'INTERNAL_SERVER_ERROR'
    ): ResponseInterface {
        return $this->errorResponse(
            $message,
            500,
            $code,
            []
        );
    }
}

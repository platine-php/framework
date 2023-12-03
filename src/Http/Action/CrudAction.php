<?php

/**
 * Platine PHP
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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
 *  @file CrudAction.php
 *
 *  The Base CRUD action class
 *
 *  @package    Platine\Framework\Http\Action
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Action;

use Exception;
use Platine\Framework\Form\Param\BaseParam;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Orm\Repository;
use Platine\Pagination\Pagination;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Template;

/**
* @class CrudAction
* @package Platine\Framework\Http\Action
* @template TEntity as \Platine\Orm\Entity
*/
class CrudAction
{
    /**
    * The Lang instance
    * @var Lang
    */
    protected Lang $lang;

    /**
    * The Pagination instance
    * @var Pagination
    */
    protected Pagination $pagination;

    /**
    * The Template instance
    * @var Template
    */
    protected Template $template;

    /**
    * The Flash instance
    * @var Flash
    */
    protected Flash $flash;

    /**
    * The RouteHelper instance
    * @var RouteHelper
    */
    protected RouteHelper $routeHelper;

    /**
    * The LoggerInterface instance
    * @var LoggerInterface
    */
    protected LoggerInterface $logger;

    /**
    * The Repository instance
    * @var Repository<TEntity>
    */
    protected Repository $repository;

    /**
     * The entity fields
     * @var array<int|string, string>
     */
    protected array $fields = [];

    /**
     * The order fields
     * @var array<int|string, string>
     */
    protected array $orderFields = [];

    /**
     * The unique fields
     * @var array<int|string, string>
     */
    protected array $uniqueFields = [];

    /**
     * The template prefix
     * @var string
     */
    protected string $templatePrefix = '';

    /**
     * The route name prefix
     * @var string
     */
    protected string $routePrefix = '';

    /**
     * The entity context name
     * @var string
     */
    protected string $entityContextName = 'entity';

    /**
     * The entity record not found error message
     * @var string
     */
    protected string $messageNotFound = 'This record doesn\'t exist';

    /**
     * The entity duplicate record error message
     * @var string
     */
    protected string $messageDuplicate = 'This record already exist';

    /**
     * The entity record process error message
     * @var string
     */
    protected string $messageProcessError = 'Data processing error';

    /**
     * The entity record create message
     * @var string
     */
    protected string $messageCreate = 'Data successfully created';

    /**
     * The entity record update message
     * @var string
     */
    protected string $messageUpdate = 'Data successfully updated';

    /**
     * The entity record delete message
     * @var string
     */
    protected string $messageDelete = 'Data successfully deleted';

    /**
     * The form parameter class
     * @var class-string<\Platine\Framework\Form\Param\BaseParam<TEntity>>
     */
    protected string $paramClass;

    /**
     * The form validator class
     * @var class-string<\Platine\Framework\Form\Validator\AbstractValidator>
     */
    protected string $validatorClass;

    /**
    * Create new instance
    * @param Lang $lang
    * @param Pagination $pagination
    * @param Template $template
    * @param Flash $flash
    * @param RouteHelper $routeHelper
    * @param LoggerInterface $logger
    */
    public function __construct(
        Lang $lang,
        Pagination $pagination,
        Template $template,
        Flash $flash,
        RouteHelper $routeHelper,
        LoggerInterface $logger
    ) {
        $this->lang = $lang;
        $this->pagination = $pagination;
        $this->template = $template;
        $this->flash = $flash;
        $this->routeHelper = $routeHelper;
        $this->logger = $logger;
    }

    /**
    * List all entities
    * @param ServerRequestInterface $request
    * @return ResponseInterface
    */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $context = $this->getTemplateData();
        $templateName = sprintf('%s/list', $this->templatePrefix);
        $param = new RequestData($request);
        $totalItems = $this->repository->query()
                                        ->count('id');

        $currentPage = (int) $param->get('page', 1);

        $this->pagination->setTotalItems($totalItems)
                         ->setCurrentPage($currentPage);

        $limit = $this->pagination->getItemsPerPage();
        $offset = $this->pagination->getOffset();

        $query = $this->repository->query();
        $query->offset($offset)
               ->limit($limit);

        if (count($this->orderFields) > 0) {
            foreach ($this->orderFields as $field => $dir) {
                if (is_int($field)) {
                    $query->orderBy($dir);
                } else {
                    $query->orderBy($field, $dir);
                }
            }
        }

        $results = $query->all();

        $context['list'] = $results;
        $context['pagination'] = $this->pagination->render();


        return new TemplateResponse(
            $this->template,
            $templateName,
            $context
        );
    }

    /**
    * List entity detail
    * @param ServerRequestInterface $request
    * @return ResponseInterface
    */
    public function detail(ServerRequestInterface $request): ResponseInterface
    {
        $routeListName = sprintf('%s_list', $this->routePrefix);
        $templateName = sprintf('%s/detail', $this->templatePrefix);
        $context = $this->getTemplateData();
        $id = (int) $request->getAttribute('id');

        /** @var TEntity|null $entity */
        $entity = $this->repository->find($id);

        if ($entity === null) {
            $this->flash->setError($this->lang->tr($this->messageNotFound));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        }
        $context[$this->entityContextName] = $entity;

        return new TemplateResponse(
            $this->template,
            $templateName,
            $context
        );
    }

    /**
    * Create new entity
    * @param ServerRequestInterface $request
    * @return ResponseInterface
    */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $routeListName = sprintf('%s_list', $this->routePrefix);
        $templateName = sprintf('%s/create', $this->templatePrefix);
        $context = $this->getTemplateData();
        $param = new RequestData($request);

        $formParam = new $this->paramClass($param->posts());
        $context['param'] = $formParam;

        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                $templateName,
                $context
            );
        }

        $validator = new $this->validatorClass($formParam, $this->lang);
        if ($validator->validate() === false) {
            $context['errors'] = $validator->getErrors();

            return new TemplateResponse(
                $this->template,
                $templateName,
                $context
            );
        }

        if (count($this->uniqueFields) > 0) {
            $entityExist = $this->repository->findBy($this->getEntityFields(
                $this->uniqueFields,
                $formParam
            ));

            if ($entityExist !== null) {
                $this->flash->setError($this->lang->tr($this->messageDuplicate));

                return new TemplateResponse(
                    $this->template,
                    $templateName,
                    $context
                );
            }
        }

        /** @var TEntity $entity */
        $entity = $this->repository->create($this->getEntityFields(
            $this->fields,
            $formParam
        ));
        try {
            $this->repository->save($entity);

            $this->flash->setSuccess($this->lang->tr($this->messageCreate));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        } catch (Exception $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr($this->messageProcessError));

            return new TemplateResponse(
                $this->template,
                $templateName,
                $context
            );
        }
    }

    /**
    * Update existing entity
    * @param ServerRequestInterface $request
    * @return ResponseInterface
    */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $routeListName = sprintf('%s_list', $this->routePrefix);
        $templateName = sprintf('%s/update', $this->templatePrefix);
        $context = $this->getTemplateData();
        $param = new RequestData($request);

        $id = (int) $request->getAttribute('id');

        /** @var TEntity|null $entity */
        $entity = $this->repository->find($id);

        if ($entity === null) {
            $this->flash->setError($this->lang->tr($this->messageNotFound));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        }
        $context[$this->entityContextName] = $entity;
        $context['param'] = (new $this->paramClass())->fromEntity($entity);
        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                $templateName,
                $context
            );
        }
        $formParam = new $this->paramClass($param->posts());
        $context['param'] = $formParam;

        $validator = new $this->validatorClass($formParam, $this->lang);
        if ($validator->validate() === false) {
            $context['errors'] = $validator->getErrors();

            return new TemplateResponse(
                $this->template,
                $templateName,
                $context
            );
        }

        if (count($this->uniqueFields) > 0) {
            $entityExist = $this->repository->findBy($this->getEntityFields(
                $this->uniqueFields,
                $formParam
            ));

            if ($entityExist !== null && $entityExist->id !== $id) {
                $this->flash->setError($this->lang->tr($this->messageDuplicate));

                return new TemplateResponse(
                    $this->template,
                    $templateName,
                    $context
                );
            }
        }

        $fields = $this->getEntityFields($this->fields, $formParam);
        foreach ($fields as $field => $value) {
            $entity->{$field} = $value;
        }
        try {
            $this->repository->save($entity);

            $this->flash->setSuccess($this->lang->tr($this->messageUpdate));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        } catch (Exception $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr($this->messageProcessError));

            return new TemplateResponse(
                $this->template,
                $templateName,
                $context
            );
        }
    }

    /**
    * Delete the entity
    * @param ServerRequestInterface $request
    * @return ResponseInterface
    */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $routeListName = sprintf('%s_list', $this->routePrefix);
        $id = (int) $request->getAttribute('id');

        /** @var TEntity|null $entity */
        $entity = $this->repository->find($id);

        if ($entity === null) {
            $this->flash->setError($this->lang->tr($this->messageNotFound));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        }

        try {
            $this->repository->delete($entity);

            $this->flash->setSuccess($this->lang->tr($this->messageDelete));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        } catch (Exception $ex) {
            $this->logger->error('Error when delete the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr($this->messageProcessError));

            return new RedirectResponse(
                $this->routeHelper->generateUrl($routeListName)
            );
        }
    }

    /**
     * Return the entity fields
     * @param array<int|string, string> $fields
     * @param BaseParam<TEntity> $param
     * @return array<string, mixed>
     */
    protected function getEntityFields(array $fields, BaseParam $param): array
    {
        $results = [];

        foreach ($fields as $field => $paramName) {
            if (is_int($field)) {
                $field = $paramName;
            }

            $paramMethodName = sprintf('get%s', Str::camel($paramName, false));
            $results[$field] = $param->{$paramMethodName}();
        }

        return $results;
    }

    /**
     * Return the additional template data
     * @return array<string, mixed>
     */
    protected function getTemplateData(): array
    {
        return [];
    }
}

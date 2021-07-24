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
 *  @file ListAction.php
 *
 *  The List action class
 *
 *  @package    Platine\Framework\Demo\Action\Role
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Action\Role;

use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Pagination\Pagination;
use Platine\Template\Template;

/**
 * @class ListAction
 * @package Platine\Framework\Demo\Action\Role
 * @template T
 */
class ListAction implements RequestHandlerInterface
{
    /**
     * The role repository
     * @var RoleRepository
     */
    protected RoleRepository $roleRepository;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
     * The pagination instance
     * @var Pagination
     */
    protected Pagination $pagination;

    /**
     * Create new instance
     * @param Template $template
     * @param RoleRepository $roleRepository
     * @param Pagination $pagination
     */
    public function __construct(
        Template $template,
        RoleRepository $roleRepository,
        Pagination $pagination
    ) {
        $this->roleRepository = $roleRepository;
        $this->template = $template;
        $this->pagination = $pagination;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $param = new RequestData($request);

        ////////// BEGIN PAGINATION //////////////////
        $totalItems = $this->roleRepository->query()
                                            ->count('id');
        $currentPage = (int)$param->get('page', 1);
        $this->pagination->setTotalItems($totalItems)
                         ->setCurrentPage($currentPage);
        $limit = $this->pagination->getItemsPerPage();
        $offset = $this->pagination->getOffset();
        ////////// END PAGINATION //////////////////

        $roles = $this->roleRepository
                                      ->limit($offset, $limit)
                                      ->all();

        return new TemplateResponse(
            $this->template,
            'role/list',
            [
                'roles' => $roles,
                'pagination' => $this->pagination->render()
            ]
        );
    }
}

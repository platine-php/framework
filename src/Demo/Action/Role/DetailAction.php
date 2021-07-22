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
 *  @file DetailAction.php
 *
 *  The Detail action class
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
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * @class DetailAction
 * @package Platine\Framework\Demo\Action\Role
 * @template T
 */
class DetailAction implements RequestHandlerInterface
{

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The translator instance
     * @var Lang
     */
    protected Lang $lang;

    /**
     * The role repository instance
     * @var RoleRepository
     */
    protected RoleRepository $roleRepository;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
     * The route helper instance
     * @var RouteHelper
     */
    protected RouteHelper $routeHelper;

    /**
     * The session instance
     * @var Session
     */
    protected Session $session;

    /**
     * Create new instance
     * @param Lang $lang
     * @param Session $session
     * @param LoggerInterface $logger
     * @param Template $template
     * @param RoleRepository $roleRepository
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        Lang $lang,
        Session $session,
        LoggerInterface $logger,
        Template $template,
        RoleRepository $roleRepository,
        RouteHelper $routeHelper
    ) {
        $this->lang = $lang;
        $this->session = $session;
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $role = $this->roleRepository
                     ->with('permissions')
                     ->find($id);
        if (!$role) {
            $this->session->setFlash('error', $this->lang->tr('Can not find the role'));
            $this->logger->warning('Can not find role with id {id}', ['id' => $id]);

            return new RedirectResponse(
                $this->routeHelper->generateUrl('role_list')
            );
        }

        return new TemplateResponse(
            $this->template,
            'role/detail',
            [
                'role' => $role,
            ]
        );
    }
}

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
 *  @file CreateAction.php
 *
 *  The Create action class
 *
 *  @package    Platine\Framework\Demo\Action\Permission
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Action\Permission;

use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Repository\PermissionRepository;
use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Framework\Demo\Form\Param\PermissionParam;
use Platine\Framework\Demo\Form\Validator\PermissionValidator;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * @class CreateAction
 * @package Platine\Framework\Demo\Action\Permission
 * @template T
 */
class CreateAction implements RequestHandlerInterface
{

    /**
     * Logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The session instance
     * @var Session
     */
    protected Session $session;

    /**
     * The permission repository instance
     * @var PermissionRepository
     */
    protected PermissionRepository $permissionRepository;

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
     * Create new instance
     * @param LoggerInterface $logger
     * @param Session $session
     * @param Template $template
     * @param PermissionRepository $permissionRepository
     * @param RoleRepository $roleRepository
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Session $session,
        Template $template,
        PermissionRepository $permissionRepository,
        RoleRepository $roleRepository,
        RouteHelper $routeHelper
    ) {
        $this->logger = $logger;
        $this->session = $session;
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $permissions = $this->permissionRepository
                                                  ->orderBy('code')
                                                  ->all();

        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'permission/create',
                [
                    'param' => new PermissionParam([]),
                    'permissions' => $permissions
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new PermissionParam($param->posts());
        $validator = new PermissionValidator($formParam);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'permission/create',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam,
                    'permissions' => $permissions
                ]
            );
        }

        $code = $param->post('code');
        $permissionExist = $this->permissionRepository->findBy(['code' => $code]);

        if ($permissionExist) {
            $this->logger->error('Permission with code {code} already exists', ['code' => $code]);
            $this->session->setFlash('error', 'This permission already exists');
            return new TemplateResponse(
                $this->template,
                'permission/create',
                [
                   'param' => $formParam,
                   'permissions' => $permissions
                ]
            );
        }

        /** @var Permission $permission */
        $permission = $this->permissionRepository->create([
            'code' => $formParam->getCode(),
            'description' => $formParam->getDescription(),
            'depend' => $formParam->getDepend(),
        ]);

        $result = $this->permissionRepository->save($permission);

        if (!$result) {
            $this->session->setFlash('error', 'Error when saved the permission');
            $this->logger->error('Error when saved the permission');
            return new TemplateResponse(
                $this->template,
                'permission/create',
                [
                   'param' => $formParam,
                   'permissions' => $permissions
                ]
            );
        }


        $this->session->setFlash('success', 'Permission saved successfully');

        return new RedirectResponse(
            $this->routeHelper->generateUrl('permission_list')
        );
    }
}

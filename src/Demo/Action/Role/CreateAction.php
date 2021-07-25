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

use Platine\Framework\Auth\Entity\Role;
use Platine\Framework\Auth\Repository\PermissionRepository;
use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Framework\Demo\Form\Param\RoleParam;
use Platine\Framework\Demo\Form\Validator\RoleValidator;
use Platine\Framework\Http\RequestData;
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
 * @class CreateAction
 * @package Platine\Framework\Demo\Action\Role
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
     * The permission repository
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
     * @param Lang $lang
     * @param LoggerInterface $logger
     * @param Session $session
     * @param Template $template
     * @param RoleRepository $roleRepository
     * @param PermissionRepository $permissionRepository
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        Lang $lang,
        LoggerInterface $logger,
        Session $session,
        Template $template,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        RouteHelper $routeHelper
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->session = $session;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
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
                'role/create',
                [
                    'param' => new RoleParam([]),
                    'permissions' => $permissions
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new RoleParam($param->posts());
        $validator = new RoleValidator($formParam, $this->lang);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'role/create',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam,
                    'permissions' => $permissions
                ]
            );
        }

        $name = $param->post('name');
        $roleExist = $this->roleRepository->findBy(['name' => $name]);

        if ($roleExist) {
            $this->logger->error('Role with name {name} already exists', ['name' => $name]);
            $this->session->setFlash('error', $this->lang->tr('This role already exists'));
            return new TemplateResponse(
                $this->template,
                'role/create',
                [
                   'param' => $formParam,
                   'permissions' => $permissions
                ]
            );
        }

        /** @var Role $role */
        $role = $this->roleRepository->create([
            'name' => $formParam->getName(),
            'description' => $formParam->getDescription()
        ]);

         //Handle permissions
        $permissionsId = $param->post('permissions', []);
        if (!empty($permissionsId)) {
            $selectedPermissions = $this->permissionRepository->findAll(...$permissionsId);
            $role->setPermissions($selectedPermissions);
        }
        ///////////////////

        $result = $this->roleRepository->save($role);

        if (!$result) {
            $this->session->setFlash('error', $this->lang->tr('Error when saved the role'));
            $this->logger->error('Error when saved the role');
            return new TemplateResponse(
                $this->template,
                'role/create',
                [
                   'param' => $formParam,
                   'permissions' => $permissions
                ]
            );
        }


        $this->session->setFlash('success', $this->lang->tr('Role saved successfully'));

        return new RedirectResponse(
            $this->routeHelper->generateUrl('role_list')
        );
    }
}

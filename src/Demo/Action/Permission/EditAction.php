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
 *  @file EditAction.php
 *
 *  The Edit action class
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
use Platine\Framework\Demo\Form\Param\PermissionParam;
use Platine\Framework\Demo\Form\Validator\PermissionValidator;
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
 * @class EditAction
 * @package Platine\Framework\Demo\Action\Permission
 * @template T
 */
class EditAction implements RequestHandlerInterface
{

    /**
     * The logger instance
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
     * @param PermissionRepository $permissionRepository
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        Lang $lang,
        LoggerInterface $logger,
        Session $session,
        Template $template,
        PermissionRepository $permissionRepository,
        RouteHelper $routeHelper
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->session = $session;
        $this->permissionRepository = $permissionRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');

        /** @var ?Permission $permission */
        $permission = $this->permissionRepository->find($id);
        if (!$permission) {
            $this->session->setFlash('error', $this->lang->tr('Can not find the permission'));
            $this->logger->warning('Can not find permission with id {id}', ['id' => $id]);

            return new RedirectResponse(
                $this->routeHelper->generateUrl('permission_list')
            );
        }

        $permissions = $this->permissionRepository
                                                  ->orderBy('code')
                                                  ->all();


        $entityToFormParam = (new PermissionParam())->fromEntity($permission);

        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'permission/edit',
                [
                    'param' => $entityToFormParam,
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
                'permission/edit',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam,
                    'permissions' => $permissions
                ]
            );
        }

        $code = $param->post('code');
        $permissionExist = $this->permissionRepository->findBy(['code' => $code]);

        if ($permissionExist && $permissionExist->id != $id) {
            $this->session->setFlash('error', $this->lang->tr('This permission already exists'));
            $this->logger->error('Permission with code {code} already exists', ['code' => $code]);
            return new TemplateResponse(
                $this->template,
                'permission/edit',
                [
                   'param' => $formParam,
                   'permissions' => $permissions
                ]
            );
        }

        $permission->code = $formParam->getCode();
        $permission->description = $formParam->getDescription();
        $permission->depend = $formParam->getDepend();

        $result = $this->permissionRepository->save($permission);

        if (!$result) {
            $this->session->setFlash('error', $this->lang->tr('Error when saved the permission'));
            $this->logger->error('Error when saved the permission');
            return new TemplateResponse(
                $this->template,
                'permission/edit',
                [
                   'param' => $formParam,
                   'permissions' => $permissions
                ]
            );
        }

        $this->session->setFlash('success', $this->lang->tr('Permission saved successfully'));

        return new RedirectResponse(
            $this->routeHelper->generateUrl('permission_list')
        );
    }
}

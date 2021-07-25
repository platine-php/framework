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
 *  @package    Platine\Framework\Demo\Action\User
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Demo\Form\Param\UserParam;
use Platine\Framework\Demo\Form\Validator\UserValidator;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Security\Hash\HashInterface;
use Platine\Session\Session;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Template;

/**
 * @class EditAction
 * @package Platine\Framework\Demo\Action\User
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
     * The user repository
     * @var UserRepository
     */
    protected UserRepository $userRepository;

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
     * The route helper instance
     * @var RouteHelper
     */
    protected RouteHelper $routeHelper;

     /**
     * The password hash to be used
     * @var HashInterface
     */
    protected HashInterface $hash;

    /**
     * Create new instance
     * @param Lang $lang
     * @param LoggerInterface $logger
     * @param Session $session
     * @param Template $template
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param RouteHelper $routeHelper
     * @param HashInterface $hash
     */
    public function __construct(
        Lang $lang,
        LoggerInterface $logger,
        Session $session,
        Template $template,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        RouteHelper $routeHelper,
        HashInterface $hash
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
        $this->hash = $hash;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');

        /** @var ?User $user */
        $user = $this->userRepository
                                    ->with('roles')
                                    ->find($id);
        if (!$user) {
            $this->session->setFlash('error', $this->lang->tr('Can not find the user'));
            $this->logger->warning('Can not find user with id {id}', ['id' => $id]);

            return new RedirectResponse(
                $this->routeHelper->generateUrl('user_list')
            );
        }

        $statusList = [
            'D' => 'Deactive',
            'A' => 'Active',
        ];

        $roles = $this->roleRepository->all();

        $currentRolesId = [];
        foreach ($user->roles as $entity) {
            $currentRolesId[] = $entity->id;
        }

        $entityToFormParam = (new UserParam())->fromEntity($user);
        $entityToFormParam->setRoles($currentRolesId);

        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                    'param' => $entityToFormParam,
                    'status' => $statusList,
                    'roles' => $roles
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new UserParam($param->posts());
        $validator = new UserValidator($formParam, $this->lang);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam,
                    'status' => $statusList,
                    'roles' => $roles
                ]
            );
        }

        $username = $param->post('username');
        $userExist = $this->userRepository->findBy(['username' => $username]);

        if ($userExist && $userExist->id != $id) {
            $this->session->setFlash('error', $this->lang->tr('This user already exists'));
            $this->logger->error('User with username {username} already exists', ['username' => $username]);
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                   'param' => $formParam,
                   'status' => $statusList,
                   'roles' => $roles
                ]
            );
        }

        $password = $param->post('password');

        $user->username = $formParam->getUsername();
        $user->firstname = Str::ucfirst($formParam->getFirstname());
        $user->lastname = Str::upper($formParam->getLastname());
        $user->role = $formParam->getRole();
        $user->email = $formParam->getEmail();
        $user->status = $formParam->getStatus();

        if (!empty($password)) {
            $passwordHash = $this->hash->hash($password);
            $user->password = $passwordHash;
        }

        //Handle roles
        $rolesId = $param->post('roles', []);
        $rolesIdToDelete = array_diff($currentRolesId, $rolesId);
        if (!empty($rolesIdToDelete)) {
            $deletedRoles = $this->roleRepository->findAll(...$rolesIdToDelete);
            $user->removeRoles($deletedRoles);
        }
        $newRolesId = array_diff($rolesId, $currentRolesId);
        if (!empty($newRolesId)) {
            $newRoles = $this->roleRepository->findAll(...$newRolesId);
            $user->setRoles($newRoles);
        }
        /////////////////

        $result = $this->userRepository->save($user);

        if (!$result) {
            $this->session->setFlash('error', $this->lang->tr('Error when saved the user'));
            $this->logger->error('Error when saved the user');
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                   'param' => $formParam,
                   'status' => $statusList,
                   'roles' => $roles
                ]
            );
        }

        $this->session->setFlash('success', $this->lang->tr('User saved successfully'));

        $returnUrl = $this->routeHelper->generateUrl('user_list');
        if ($param->get('from_detail')) {
            $returnUrl = $this->routeHelper->generateUrl('user_detail', ['id' => $id]);
        }
        return new RedirectResponse($returnUrl);
    }
}

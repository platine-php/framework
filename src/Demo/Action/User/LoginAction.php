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
 *  @file LoginAction.php
 *
 *  The Login action class
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

use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\AuthenticationException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Demo\Form\Param\AuthParam;
use Platine\Framework\Demo\Form\Validator\AuthValidator;
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
 * @class LoginAction
 * @package Platine\Framework\Demo\Action\User
 * @template T
 */
class LoginAction implements RequestHandlerInterface
{

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
     * The route helper
     * @var RouteHelper
     */
    protected RouteHelper $routeHelper;

    /**
     * The authentication instance
     * @var AuthenticationInterface
     */
    protected AuthenticationInterface $authentication;

    /**
     * The session instance
     * @var Session
     */
    protected Session $session;

    /**
     * Create new instance
     * @param AuthenticationInterface $authentication
     * @param Session $session
     * @param LoggerInterface $logger
     * @param Template $template
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        AuthenticationInterface $authentication,
        Session $session,
        LoggerInterface $logger,
        Template $template,
        RouteHelper $routeHelper
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
        $this->authentication = $authentication;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'user/login',
                [
                    'param' => new AuthParam([])
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new AuthParam($param->posts());
        $validator = new AuthValidator($formParam);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'user/login',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam
                ]
            );
        }

        $username = $param->post('username');
        $password = $param->post('password');

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        try {
            $this->authentication->login($credentials);
        } catch (
            MissingCredentialsException
                | AccountNotFoundException
                | InvalidCredentialsException
                | AccountLockedException $ex
        ) {
            return $this->getExceptionResponse($ex, $formParam);
        }

        return new RedirectResponse(
            $this->routeHelper->generateUrl('home')
        );
    }

    /**
     * Return response when authentication throw Exception
     * @param AuthenticationException $ex
     * @param AuthParam $formParam
     * @return ResponseInterface
     */
    protected function getExceptionResponse(
        AuthenticationException $ex,
        AuthParam $formParam
    ): ResponseInterface {
        $this->logger->error('Authentication Error', ['exception' => $ex]);
        $this->session->setFlash('error', $ex->getMessage());
        return new TemplateResponse(
            $this->template,
            'user/login',
            [
               'param' => $formParam
            ]
        );
    }
}

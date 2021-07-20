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
 *  @file LogoutAction.php
 *
 *  The Logout action class
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
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;

/**
 * @class LogoutAction
 * @package Platine\Framework\Demo\Action\User
 * @template T
 */
class LogoutAction implements RequestHandlerInterface
{
    /**
     * The route helper instance
     * @var RouteHelper
     */
    protected RouteHelper $routeHelper;

    /**
     * The authentication instance
     * @var AuthenticationInterface
     */
    protected AuthenticationInterface $authentication;

    /**
     * Create new instance
     * @param AuthenticationInterface $authentication
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        AuthenticationInterface $authentication,
        RouteHelper $routeHelper
    ) {
        $this->routeHelper = $routeHelper;
        $this->authentication = $authentication;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->authentication->logout();

        $res = new Response();

        $loginUrl = $this->routeHelper->generateUrl('user_login');

        $res->getBody()
                ->write(sprintf('You are successfully logout 
                    <br /><a href = \'%s\'>Login Page</a>', $loginUrl));

        return $res;
    }
}

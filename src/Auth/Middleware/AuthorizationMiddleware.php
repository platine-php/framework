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
 *  @file AuthorizationMiddleware.php
 *
 *  The Authorization middleware class
 *
 *  @package    Platine\Framework\Auth\Middleware
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Auth\Middleware;

use Platine\Config\Config;
use Platine\Framework\Auth\AuthorizationInterface;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Route;

/**
 * @class AuthorizationMiddleware
 * @package Platine\Framework\Auth\Middleware
 * @template T
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * Create new instance
     * @param AuthorizationInterface $authorization
     * @param Config<T> $config
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        protected AuthorizationInterface $authorization,
        protected Config $config,
        protected RouteHelper $routeHelper
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        //If no route has been match no need check for authorization
        /** @var Route|null $route */
        $route = $request->getAttribute(Route::class);
        if ($route === null) {
            return $handler->handle($request);
        }

        $permission = $route->getAttribute('permission');

        if (empty($permission)) {
            return $handler->handle($request);
        }

        if ($this->authorization->isGranted($permission) === false) {
            $unauthorizedRoute = $this->config->get('auth.authorization.unauthorized_route');

            return new RedirectResponse(
                $this->routeHelper->generateUrl($unauthorizedRoute)
            );
        }

        return $handler->handle($request);
    }
}

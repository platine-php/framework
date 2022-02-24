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
 *  @file ApiAuthenticationMiddleware.php
 *
 *  The API Authentication middleware class
 *
 *  @package    Platine\Framework\Auth\Middleware
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Auth\Middleware;

use Platine\Config\Config;
use Platine\Framework\Auth\ApiAuthenticationInterface;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Route;

/**
 * @class ApiAuthenticationMiddleware
 * @package Platine\Framework\Auth\Middleware
 * @template T
 */
class ApiAuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * The Authentication instance
     * @var ApiAuthenticationInterface
     */
    protected ApiAuthenticationInterface $authentication;

    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * Create new instance
     * @param ApiAuthenticationInterface $authentication
     * @param Config<T> $config
     */
    public function __construct(
        ApiAuthenticationInterface $authentication,
        Config $config
    ) {
        $this->authentication = $authentication;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$this->shouldBeProcessed($request)) {
            return $handler->handle($request);
        }

        if (!$this->authentication->isAuthenticated($request)) {
            return $this->unauthorizedResponse();
        }

        return $handler->handle($request);
    }

    /**
     * Whether we can process this request
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function shouldBeProcessed(ServerRequestInterface $request): bool
    {
        //If no route has been match no need check for authentication
        /** @var ?Route $route */
        $route = $request->getAttribute(Route::class);
        if (!$route) {
            return false;
        }

        //Check if the path match
        $path = $this->config->get('api.auth.path', '/');
        if (!preg_match('~^' . $path . '~', $route->getPattern())) {
            return false;
        }

        //check if is url whitelist
        $urls = $this->config->get('api.auth.url_whitelist', []);
        if (in_array($route->getName(), $urls)) {
            return false;
        }

        return true;
    }

    /**
     * Return an unauthorized response
     * @return ResponseInterface
     */
    protected function unauthorizedResponse(): ResponseInterface
    {
        return new RestResponse([], [], false, 4010, 'User is not authenticated', 401);
    }
}

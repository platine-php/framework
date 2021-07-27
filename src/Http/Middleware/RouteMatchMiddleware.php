<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant PHP
 * Web framework
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
 *  @file RouteMatchMiddleware.php
 *
 *  The Route match middleware class is used to match the request
 * based on the routes definition
 *
 *  @package    Platine\Framework\Http\Middleware
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Middleware;

use Platine\Framework\Http\Exception\HttpMethodNotAllowedException;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Route;
use Platine\Route\Router;

/**
 * @class RouteMatchMiddleware
 * @package Platine\Framework\Http\Middleware
 */
class RouteMatchMiddleware implements MiddlewareInterface
{

    /**
     * The Router instance
     * @var Router
     */
    protected Router $router;

    /**
     * The list of allowed methods
     * @var array<string>
     */
    protected array $allowedMethods = [];

    /**
     * Create new instance
     * @param Router $router
     * @param array<string>  $allowedMethods the default allowed methods
     */
    public function __construct(Router $router, array $allowedMethods = ['HEAD'])
    {
        $this->router = $router;

        foreach ($allowedMethods as $method) {
            if (is_string($method)) {
                $this->allowedMethods[] = strtoupper($method);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$route = $this->router->match($request, false)) {
            return $handler->handle($request);
        }

        if (
            !$this->isAllowedMethod($request->getMethod())
            && !$route->isAllowedMethod($request->getMethod())
        ) {
            $exception = new HttpMethodNotAllowedException($request);
            $exception->setAllowedMethods($route->getMethods());

            throw $exception;
        }

        foreach ($route->getParameters()->all() as $parameter) {
            $request = $request->withAttribute(
                $parameter->getName(),
                $parameter->getValue()
            );
        }

        $request = $request->withAttribute(Route::class, $route);

        return $handler->handle($request);
    }

    /**
     * Check whether the given method is allowed
     * @param  string  $method
     * @return bool
     */
    protected function isAllowedMethod(string $method): bool
    {
        return (empty($this->allowedMethods)
                || in_array(strtoupper($method), $this->allowedMethods, true));
    }
}

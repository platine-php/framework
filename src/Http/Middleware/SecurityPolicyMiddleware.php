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
 *  @file SecurityPolicyMiddleware.php
 *
 *  The Security Policy middleware class is used to set response security headers
 *
 *  @package    Platine\Framework\Http\Middleware
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Middleware;

use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Route;

/**
 * @class SecurityPolicyMiddleware
 * @package Platine\Framework\Http\Middleware
 * @template T
 */
class SecurityPolicyMiddleware implements MiddlewareInterface
{
    /**
     * The SecurityPolicy instance
     * @var SecurityPolicy
     */
    protected SecurityPolicy $securityPolicy;

    /**
     * Create new instance
     * @param SecurityPolicy $securityPolicy
     */
    public function __construct(SecurityPolicy $securityPolicy)
    {
        $this->securityPolicy = $securityPolicy;
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

        $this->request = $request;
        
        // Generate the nonces to be used in script and style
        $scriptNonce = $this->securityPolicy->nonce('script');
        $styleNonce = $this->securityPolicy->nonce('style');

        $request = $request->withAttribute(SecurityPolicy::class, [
            'nonces' => [
                'style' => $styleNonce,
                'script' => $scriptNonce,
            ]
        ]);

        $response = $handler->handle($request);

        $headers = $this->securityPolicy->headers();
        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }

    /**
     * Whether we can process this request
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function shouldBeProcessed(ServerRequestInterface $request): bool
    {
       //If no route has been match no need check for CSRF
        /** @var ?Route $route */
        $route = $request->getAttribute(Route::class);
        if (!$route) {
            return false;
        }

        return true;
    }
}

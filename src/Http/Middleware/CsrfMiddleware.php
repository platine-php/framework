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
 *  @file CsrfMiddleware.php
 *
 *  The CSRF middleware class is used to check validation of each data send using
 *  HTTP method not safe like PUT, POST
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

use Platine\Config\Config;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Route\Route;

/**
 * @class CsrfMiddleware
 * @package Platine\Framework\Http\Middleware
 * @template T
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The CSRF manager
     * @var CsrfManager<T>
     */
    protected CsrfManager $csrfManager;

    /**
     * The translator instance
     * @var Lang
     */
    protected Lang $lang;

    /**
     * The request instance to use
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Create new instance
     * @param LoggerInterface $logger
     * @param Lang $lang
     * @param Config<T> $config
     * @param CsrfManager<T> $csrfManager
     */
    public function __construct(
        LoggerInterface $logger,
        Lang $lang,
        Config $config,
        CsrfManager $csrfManager
    ) {
        $this->config = $config;
        $this->csrfManager = $csrfManager;
        $this->lang = $lang;
        $this->logger = $logger;
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

        if ($this->isValid() === false) {
            return $this->unauthorizedResponse();
        }

        return $handler->handle($request);
    }

    /**
     * Check if the request if valid
     * @return bool
     */
    protected function isValid(): bool
    {
        return $this->csrfManager->validate($this->request);
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

        // Check for route attribute (useful for GET method)
        $csrf = (bool) $route->getAttribute('csrf');

        $methods = $this->config->get('security.csrf.http_methods', []);

        if (!in_array($request->getMethod(), $methods) && $csrf === false) {
            return false;
        }

        //check if is url whitelist
        $urls = $this->config->get('security.csrf.url_whitelist', []);
        if (in_array($route->getName(), $urls)) {
            return false;
        }

        return true;
    }

    /**
     * Return the unauthorized response
     * @return ResponseInterface
     */
    protected function unauthorizedResponse(): ResponseInterface
    {
        $this->logger->error(
            'Unauthorized Request, CSRF validation failed for {method}:{url}',
            [
                'method' => $this->request->getMethod(),
                'url' => (string) $this->request->getUri(),
            ]
        );
        $response = new Response(403);

        $message = $this->lang->tr('Page expired, or request token invalid');
        $response->getBody()->write($message);

        return $response;
    }
}

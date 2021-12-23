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
 *  @file CorsMiddleware.php
 *
 *  The CORS middleware class is used to check the CORS policies
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

use Platine\Config\Config;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Route\Route;
use Platine\Stdlib\Helper\Str;

/**
 * @class CorsMiddleware
 * @package Platine\Framework\Http\Middleware
 * @template T
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The server request to use
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The response to return
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * Create new instance
     * @param LoggerInterface $logger
     * @param Config<T> $config
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config
    ) {
        $this->config = $config;
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

        if ($this->isPreflight()) {
            $response = new Response(204);

            $this->logger->info(
                'CORS Preflight Request for {method}:{url}',
                [
                    'method' => $request->getMethod(),
                    'url' => (string) $request->getUri(),
                ]
            );
        } else {
            $response = $handler->handle($request);
        }
        $this->response = $response;

        $this->setCorsHeaders();

        return $this->response;
    }

    /**
     * Whether we can process this request
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function shouldBeProcessed(ServerRequestInterface $request): bool
    {
        //If no route has been match no need check for CORS
        /** @var ?Route $route */
        $route = $request->getAttribute(Route::class);
        if (!$route) {
            return false;
        }

        //Check if the path match
        $path = $this->config->get('security.cors.path', '/');
        if (!preg_match('~^' . $path . '~', $route->getPattern())) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current request is preflight
     * @return bool
     */
    protected function isPreflight(): bool
    {
        return $this->request->getMethod() === 'OPTIONS';
    }

    /**
     * Set the CORS headers
     * @return void
     */
    protected function setCorsHeaders(): void
    {
        if ($this->isPreflight()) {
            $this->setOrigin()
                 ->setMaxAge()
                 ->setAllowCredentials()
                 ->setAllowMethods()
                 ->setAllowHeaders();
        } else {
            $this->setOrigin()
                 ->setExposedHeaders()
                 ->setAllowCredentials();
        }
    }

    /**
     * Set Origin header
     * @return $this
     */
    protected function setOrigin(): self
    {
        $origins = $this->config->get('security.cors.origins', ['*']);
        // default to the first allowed origin
        $origin = reset($origins);

        foreach ($origins as $ori) {
            if ($ori === $this->request->getHeaderLine('Origin')) {
                $origin = $ori;
                break;
            }
        }

        $this->response = $this->response
                                ->withHeader(
                                    'Access-Control-Allow-Origin',
                                    $origin
                                );

        return $this;
    }

    /**
     * Set expose headers
     * @return $this
     */
    protected function setExposedHeaders(): self
    {

        $headers = $this->config->get('security.cors.expose_headers', []);
        if (!empty($headers)) {
            $this->response = $this->response
                                ->withHeader(
                                    'Access-Control-Expose-Headers',
                                    implode(', ', $headers)
                                );
        }

        return $this;
    }

    /**
     * Set max age header
     * @return $this
     */
    protected function setMaxAge(): self
    {
        $maxAge = $this->config->get('security.cors.max_age', 1800);

        $this->response = $this->response
                            ->withHeader(
                                'Access-Control-Max-Age',
                                Str::stringify($maxAge)
                            );

        return $this;
    }

    /**
     * Set Allow credentials header
     * @return $this
     */
    protected function setAllowCredentials(): self
    {
        $allowCredentials = $this->config->get('security.cors.allow_credentials', false);

        if ($allowCredentials) {
            $this->response = $this->response
                                ->withHeader(
                                    'Access-Control-Allow-Credential',
                                    Str::stringify($allowCredentials)
                                );
        }

        return $this;
    }

    /**
     * Set allow methods header
     * @return $this
     */
    protected function setAllowMethods(): self
    {
        $methods = $this->config->get('security.cors.allow_methods', []);

        if (!empty($methods)) {
            $this->response = $this->response
                                ->withHeader(
                                    'Access-Control-Allow-Methods',
                                    implode(', ', $methods)
                                );
        }

        return $this;
    }

    /**
     * Set Allow headers
     * @return $this
     */
    protected function setAllowHeaders(): self
    {

        $headers = $this->config->get('security.cors.allow_headers', []);

        if (empty($headers)) {
            //use request headers
            $requestHeaders = $this->request
                                    ->getHeaderLine('Access-Control-Request-Headers');

            if (!empty($requestHeaders)) {
                $headers = $requestHeaders;
            }
        }
        if (!empty($headers)) {
            if (is_array($headers)) {
                $headers = implode(', ', $headers);
            }

            $this->response = $this->response
                                ->withHeader(
                                    'Access-Control-Allow-Headers',
                                    $headers
                                );
        }

        return $this;
    }
}

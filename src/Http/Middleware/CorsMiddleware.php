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
        if ($request->getMethod() !== 'OPTIONS') {
            return $handler->handle($request);
        }

        $this->logger->info(
            'CORS Request for {method}:{url}',
            [
                'method' => $request->getMethod(),
                'url' => (string) $request->getUri(),
            ]
        );

        return $this->corsResponse();
    }

    /**
     * Return the CORS response
     * @return ResponseInterface
     */
    protected function corsResponse(): ResponseInterface
    {
        $origin = $this->config->get('security.cors.origin', '*');
        $headers = $this->config->get('security.cors.headers', [
            'Origin',
            'X-Requested-With',
            'Content-Type',
            'Accept',
            'Connection',
            'User-Agent',
            'Cookie',
            'Cache-Control',
            'token',
        ]);
        $methods = $this->config->get(
            'security.cors.methods',
            ['GET', 'OPTIONS', 'HEAD', 'PUT', 'POST', 'DELETE']
        );
        $credentials = $this->config->get('security.cors.credentials', true);
        $maxAge = $this->config->get('security.cors.max_age', 1800);

        $response = (new Response(204))
                            ->withHeader('Access-Control-Allow-Credentials', Str::stringify($credentials))
                            ->withHeader('Access-Control-Max-Age', Str::stringify($maxAge))
                            ->withHeader('Access-Control-Allow-Origin', $origin)
                            ->withHeader('Access-Control-Allow-Methods', implode(', ', $methods))
                            ->withHeader('Access-Control-Allow-Headers', implode(', ', $headers));

        return $response;
    }
}

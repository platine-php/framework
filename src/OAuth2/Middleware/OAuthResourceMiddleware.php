<?php

/**
 * Platine PHP
 *
 * Platine PHP is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Middleware;

use Platine\Config\Config;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\OAuth2\Entity\AccessToken;
use Platine\OAuth2\Exception\InvalidAccessTokenException;
use Platine\OAuth2\ResourceServerInterface;
use Platine\OAuth2\Response\OAuthJsonResponse;
use Platine\Route\Route;

/**
 * @class OAuthResourceMiddleware
 * @package Platine\Framework\OAuth2\Middleware
 * @template T
 */
class OAuthResourceMiddleware implements MiddlewareInterface
{
    /**
     * The scope list
     * @var array<string>
     */
    protected array $scopes = [];

    /**
     * Create new instance
     *
     * @param ResourceServerInterface $resourceServer
     * @param Config<T> $config
     */
    public function __construct(
        protected ResourceServerInterface $resourceServer,
        protected Config $config
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($this->shouldBeProcessed($request) === false) {
            return $handler->handle($request);
        }

        try {
            $token = $this->resourceServer->getAccessToken($request, $this->scopes);
            if ($token === null) {
                throw InvalidAccessTokenException::invalidToken('No access token found in the request');
            }
        } catch (InvalidAccessTokenException $ex) {
            // If we're here, this means that there was an access token, but it's either expired
            // or invalid. If that's the case we must immediately return
            return new OAuthJsonResponse(
                [
                    'error' => $ex->getCode(),
                    'error_description' => $ex->getMessage(),
                ],
                401
            );
        }

        return $handler->handle($request->withAttribute(AccessToken::class, $token));
    }

    /**
     * Whether we can process this request
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function shouldBeProcessed(ServerRequestInterface $request): bool
    {
       //If no route has been match no need check
        /** @var Route|null $route */
        $route = $request->getAttribute(Route::class);
        if ($route === null) {
            return false;
        }

        $scopes = $route->getAttribute('scopes');
        $this->scopes = $scopes ?? [];

        //check if is url whitelist
        $urls = $this->config->get('oauth2.url_whitelist', []);
        if (in_array($route->getName(), $urls)) {
            return false;
        }

        return true;
    }
}

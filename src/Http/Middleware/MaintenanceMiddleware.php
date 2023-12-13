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
 *  @file MaintenanceMiddleware.php
 *
 *  The Maintenance middleware class is used to check application maintenance and
 * handle the request if needed
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
use Platine\Cookie\Cookie;
use Platine\Cookie\CookieManager;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Exception\HttpException;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Stdlib\Helper\Json;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Template;
use Throwable;

/**
 * @class MaintenanceMiddleware
 * @package Platine\Framework\Http\Middleware
 * @template T
 */
class MaintenanceMiddleware implements MiddlewareInterface
{
    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The Application instance
     * @var Application
     */
    protected Application $app;

    /**
     * Create new instance
     * @param Config<T> $config
     * @param Application $app
     */
    public function __construct(
        Config $config,
        Application $app
    ) {
        $this->config = $config;
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        if ($this->isExceptUrl($request)) {
            return $handler->handle($request);
        }

        if ($this->app->isInMaintenance()) {
            try {
                $data = $this->app->maintenance()->data();
            } catch (Throwable $ex) {
                throw $ex;
            }

            if (isset($data['secret']) && trim($request->getUri()->getPath(), '/') === $data['secret']) {
                return $this->bypassResponse($data['secret']);
            }

            if ($this->hasValidBypassCookie($request, $data)) {
                return $handler->handle($request);
            }

            if (isset($data['template'])) {
                return $this->templateResponse($data['template'], $data);
            }

            $message = $data['message'] ?? 'The server is temporarily busy, try again later';

            $httpException = new HttpException(
                $request,
                $message,
                $data['status'] ?? 503
            );

            $httpException->setTitle('Service Unavailable')
                          ->setDescription($message)
                          ->setHeaders($this->getHeaders($data));

            throw $httpException;
        }

        return $handler->handle($request);
    }

    protected function bypassResponse(string $secret): ResponseInterface
    {
        $routeHelper = $this->getRouteHelper();
        $bypassRouteName = $this->config->get('maintenance.bypass_route', '');
        $url = $routeHelper->generateUrl($bypassRouteName);

        // Cookie
        $manager = $this->getCookieManager();
        $cookie = $this->createBypassCookie($secret);

        $manager->add($cookie);
        $redirectResponse = new RedirectResponse($url);

        return $manager->send($redirectResponse);
    }

    /**
     * Return the template response
     * @param string $template
     * @param array<string, mixed> $data
     * @return ResponseInterface
     */
    protected function templateResponse(string $template, array $data): ResponseInterface
    {
        $response = new TemplateResponse(
            $this->getTemplate(),
            $template,
            $data,
            $data['status'] ?? 503
        );

        foreach ($this->getHeaders($data) as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }

    /**
     * Check whether it's request URL white list
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isExceptUrl(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        foreach ($this->getExceptUrls() as $url) {
            if (Str::is($url, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the except URLs
     * @return array<string, string>
     */
    protected function getExceptUrls(): array
    {
        return $this->config->get('maintenance.url_whitelist', []);
    }

    /**
     * Whether has valid bypass cookie
     * @param ServerRequestInterface $request
     * @param array<string, mixed> $data
     * @return bool
     */
    protected function hasValidBypassCookie(ServerRequestInterface $request, array $data): bool
    {
        if (!isset($data['secret'])) {
            return false;
        }


        $secret = $data['secret'];
        $name = $this->getCookieName();
        $cookieValue = (new RequestData($request))->cookie($name);
        if (empty($cookieValue)) {
            return false;
        }

        $payload = Json::decode(base64_decode($cookieValue), true);

        return is_array($payload) &&
                is_int($payload['expire'] ?? null) &&
                isset($payload['hash']) &&
                hash_equals(hash_hmac('sha256', (string) $payload['expire'], $secret), $payload['hash']) &&
                is_int($payload['expire']) >= time();
    }

    /**
     * Return the bypass cookie
     * @param string $secret
     * @return Cookie
     */
    protected function createBypassCookie(string $secret): Cookie
    {
        $name = $this->getCookieName();
        $expire = time() + $this->config->get('maintenance.cookie.lifetime', 43200);
        $data = [
            'expire' => $expire,
            'hash' => hash_hmac('sha256', (string) $expire, $secret)
        ];
        $value = base64_encode(Json::encode($data));
        $sessionCookieConfig = $this->config->get('session.cookie', []);

        $cookie = new Cookie(
            $name,
            $value,
            $expire,
            $sessionCookieConfig['domain'] ?? null,
            $sessionCookieConfig['path'] ?? null,
            $sessionCookieConfig['secure'] ?? false,
            true
        );

        return $cookie;
    }

    /**
     * Return the headers to be added in the response
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getHeaders(array $data): array
    {
        $headers = isset($data['retry']) ? ['Retry-After' => $data['retry']] : [];
        if (isset($data['refresh'])) {
            $headers['Refresh'] = $data['refresh'];
        }

        return $headers;
    }

    /**
     * Return the template instance
     * @return Template
     */
    protected function getTemplate(): Template
    {
        return $this->app->get(Template::class);
    }

    /**
     * Return the route helper instance
     * @return RouteHelper
     */
    protected function getRouteHelper(): RouteHelper
    {
        return $this->app->get(RouteHelper::class);
    }

    /**
     * Return the Cookie Manager instance
     * @return CookieManager
     */
    protected function getCookieManager(): CookieManager
    {
        return $this->app->get(CookieManager::class);
    }

    /**
     * Return the cookie name
     * @return string
     */
    protected function getCookieName(): string
    {
        return $this->config->get('maintenance.cookie.name', 'platine_maintenance');
    }
}

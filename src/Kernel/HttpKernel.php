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
 *  @file HttpKernel.php
 *
 *  The HTTP Kernel class
 *
 *  @package    Platine\Framework\Kernel
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Kernel;

use Platine\Config\Config;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Exception\HttpNotFoundException;
use Platine\Framework\Kernel\BaseKernel;
use Platine\Framework\Service\ServiceProvider;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\MiddlewareResolverInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Router;

/**
 * @class HttpKernel
 * @package Platine\Framework\Kernel
 * @template T
 */
class HttpKernel extends BaseKernel implements RequestHandlerInterface
{

    /**
     * The router instance
     * @var Router
     */
    protected Router $router;

    /**
     * The middleware resolver instance
     * @var MiddlewareResolverInterface
     */
    protected MiddlewareResolverInterface $middlewareResolver;

    /**
     * The list of middlewares
     * @var MiddlewareInterface[]
     */
    protected array $middlewares = [];

    /**
     * Create new instance
     * @param Application $app
     * @param Router $router
     * @param MiddlewareResolverInterface $middlewareResolver
     */
    public function __construct(
        Application $app,
        Router $router,
        MiddlewareResolverInterface $middlewareResolver
    ) {
        parent::__construct($app);
        $this->router = $router;
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * Add new middleware
     * @param  mixed $middleware
     * @return $this
     */
    public function use($middleware): self
    {
        $this->middlewares[] = $this->middlewareResolver->resolve($middleware);

        return $this;
    }

    /**
     * Run the kernel
     * @param ServerRequestInterface|null $request
     * @return void
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        $req = $request ?? ServerRequest::createFromGlobals();

        //Share the instance to use later
        $this->app->instance($req, ServerRequestInterface::class);

        //bootstrap the application
        $this->bootstrap();

        //set the routing
        $this->setRouting();

        //Load configured middlewares
        $this->registerConfiguredMiddlewares();


        /** @var EmitterInterface $emitter */
        $emitter = $this->app->get(EmitterInterface::class);
        $response = $this->handle($req);


        $emitter->emit(
            $response,
            !$this->isEmptyResponse(
                $req->getMethod(),
                $response->getStatusCode()
            )
        );
    }

    /**
     * Handle the request and generate response
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = clone $this;
        $middleware = current($handler->middlewares);
        if ($middleware === false) {
            throw new HttpNotFoundException($request);
        }
        next($handler->middlewares);

        return $middleware->process($request, $handler);
    }

    /**
     * Set routing information
     * @return void
     */
    public function setRouting(): void
    {
        /** @var Config<T> $config */
        $config = $this->app->get(Config::class);

        $basePath = $this->determineBasePath();
        $this->router->setBasePath($basePath);

        $routes = $config->get('routes', []);
        //TODO find a way to remove return of array for
        //routes configuration
        $routes[0]($this->router);

        //Load providers routes
        /** @var ServiceProvider[] $providers */
        $providers = $this->app->getProviders();
        foreach ($providers as $provider) {
            $provider->addRoutes($this->router);
        }

        $this->app->instance($this->router);
    }

    /**
     * Load configured middlewares
     * @return void
     */
    protected function registerConfiguredMiddlewares(): void
    {
        /** @var Config<T> $config */
        $config = $this->app->get(Config::class);

        /** @var string[] $middlewares */
        $middlewares = $config->get('middlewares', []);

        foreach ($middlewares as $middleware) {
            $this->use($middleware);
        }
    }

    /**
     * Will try to set application base path used somewhere in router,
     * Firstly will check if Application::getBasePath is not empty, if empty
     * check for application configuration and otherwise will try determine
     *  using server variable.
     * @return string
     */
    protected function determineBasePath(): string
    {
        $appBasePath = $this->app->getBasePath();
        if (!empty($appBasePath)) {
            return $appBasePath;
        }

        /** @var Config<T> $config */
        $config = $this->app->get(Config::class);
        $configBasePath = $config->get('app.base_path');

        if (!empty($configBasePath)) {
            $this->app->setBasePath($configBasePath);
            return $configBasePath;
        }

        //TODO
        /** @var ServerRequestInterface $request */
        $request = $this->app->get(ServerRequestInterface::class);
        $server = $request->getServerParams();
        $autoBasePath = implode('/', array_slice(explode(
            '/',
            $server['SCRIPT_NAME'] ?? ''
        ), 0, -1));
        $this->app->setBasePath($autoBasePath);
        if (!empty($autoBasePath)) {
            return $autoBasePath;
        }

        return '/';
    }

    /**
     * Whether is the response with no body
     * @param string $method
     * @param int $statusCode
     * @return bool
     */
    private function isEmptyResponse(string $method, int $statusCode): bool
    {
        return (strtoupper($method) === 'HEAD')
                || (in_array($statusCode, [100, 101, 102, 204, 205, 304], true));
    }
}

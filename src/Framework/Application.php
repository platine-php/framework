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
 *  @file Application.php
 *
 *  The Platine Application class
 *
 *  @package    Platine\Framework
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework;

use Platine\Config\Config;
use Platine\Config\FileLoader;
use Platine\Config\LoaderInterface;
use Platine\Container\ConstructorResolver;
use Platine\Container\Container;
use Platine\Container\ContainerInterface;
use Platine\Container\ResolverInterface;
use Platine\Event\Dispatcher;
use Platine\Event\DispatcherInterface;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Emitter\ResponseEmitter;
use Platine\Framework\Http\Exception\HttpNotFoundException;
use Platine\Http\Handler\CallableResolver;
use Platine\Http\Handler\CallableResolverInterface;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\MyRequestHandler;
use Platine\Route\Middleware\RouteDispatcherMiddleware;
use Platine\Route\Middleware\RouteMatchMiddleware;
use Platine\Route\Route;
use Platine\Route\RouteCollection;
use Platine\Route\Router;

/**
 * class Application
 * @package Platine\Framework
 */
class Application extends Container implements RequestHandlerInterface
{

    /**
     * The application version
     */
    public const VERSION = '1.0.0-dev';

    /**
     * The base path for this application
     * @var string
     */
    protected string $basePath;

    /**
     * The router instance
     * @var Router
     */
    protected Router $router;

    /**
     * The configuration instance
     * @var Config
     */
    protected Config $config;

    /**
     * The list of middlewares
     * @var MiddlewareInterface[]
     */
    protected array $middlewares = [];

    /**
     * Create new instance
     * @param string|null $basePath
     */
    public function __construct(?string $basePath = null)
    {
        parent::__construct();
        $this->bindCoreClasses();

        $this->config = new Config(
            $this->get(LoaderInterface::class),
            'dev'
        );

        $this->basePath = $basePath ?? $this->config->get('app.base_path', '/');

        $this->instance($this->config);

        $this->setRouting();
    }

    /**
     * Add new middleware in the list
     * @param  mixed $middleware
     * @return self
     */
    public function use($middleware): self
    {
        /** @var CallableResolverInterface $resolver */
        $resolver = $this->get(CallableResolverInterface::class);
        $this->middlewares[] = $resolver->resolve($middleware);

        return $this;
    }

    /**
     * Run the application
     * @param ServerRequestInterface|null $request
     * @return void
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        $req = $request ?? ServerRequest::createFromGlobals();
        /** @var EmitterInterface $emitter */
        $emitter = $this->get(EmitterInterface::class);
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
        $routeMatcher = $this->make(RouteMatchMiddleware::class);
        $routeDispatcher = $this->make(RouteDispatcherMiddleware::class);

        $this->use($routeMatcher);
        $this->use($routeDispatcher);

        $handler = clone $this;

        if (key($handler->middlewares) === null) {
            throw new HttpNotFoundException($request);
        }

        $middleware = current($handler->middlewares);
        next($handler->middlewares);

        return $middleware->process($request, $handler);
    }

    /**
     * Bind core classes to container
     * @return void
     */
    protected function bindCoreClasses(): void
    {
        $this->bind(ContainerInterface::class, $this);
        $this->bind(ResolverInterface::class, ConstructorResolver::class);
        $this->bind(CallableResolverInterface::class, CallableResolver::class);
        $this->bind(RequestHandlerInterface::class, $this);
        $this->bind(EmitterInterface::class, function (ContainerInterface $app) {
            return new ResponseEmitter(
                $app->get(Config::class)->get('app.response_length', null)
            );
        });
        $this->bind(DispatcherInterface::class, Dispatcher::class);
        $this->bind(LoaderInterface::class, FileLoader::class, [
            'path' => 'config'
        ]);
    }

    /**
     * Set routing information
     * @return void
     */
    protected function setRouting(): void
    {
        $routes = [
            new Route('/tnh', MyRequestHandler::class, 'home', ['GET', 'POST'])
        ]; //come from config [routes.php]

        $routeCollection = new RouteCollection($routes);

        $router = new Router($routeCollection);
        $router->setBasePath($this->basePath);

        $this->router = $router;
        $this->instance($this->router);
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

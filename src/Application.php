<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant PHP Web framework
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
 *  @package    Platine
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine;

use Platine\Config\Config;
use Platine\Config\FileLoader;
use Platine\Config\LoaderInterface;
use Platine\Container\ConstructorResolver;
use Platine\Container\Container;
use Platine\Container\ContainerInterface;
use Platine\Container\ResolverInterface;
use Platine\Http\Handler\CallableResolver;
use Platine\Http\Handler\CallableResolverInterface;
use Platine\Http\Handler\RequestHandler;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Middleware\RouteDispatcherMiddleware;
use Platine\Route\Middleware\RouteMatchMiddleware;
use Platine\Route\Route;
use Platine\Route\RouteCollection;
use Platine\Route\Router;

class Application extends Container
{

    /**
     * The application version
     */
    public const VERSION = '1.0.0-dev';

    /**
     * The base path for this application
     * @var string
     */
    protected string $basePath = '/';

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
     * The request handler
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $requestHandler;

    /**
     * Create new instance
     * @param string $basePath
     */
    public function __construct(string $basePath = '/')
    {
        parent::__construct();
        $this->basePath = $basePath;

        $this->bindCoreClasses();

        $this->config = new Config(
            $this->get(LoaderInterface::class),
            'dev'
        );

        $routes = [
            new Route('/', MyRequestHandler::class, 'home', ['GET'])
        ]; //come from config [routes.php]

        $routeCollection = new RouteCollection($routes);

        $router = new Router($routeCollection);
        $router->setBasePath($basePath);

        $this->router = $router;

        $this->instance($this->router);

        $this->requestHandler = $this->get(RequestHandlerInterface::class);
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
        $this->bind(RequestHandlerInterface::class, RequestHandler::class);
        $this->bind(LoaderInterface::class, FileLoader::class, [
            'path' => 'config'
        ]);
    }

    /**
     * Run the application
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface
    {
        $request = $request ?? ServerRequest::createFromGlobals();

        $resolver = $this->get(CallableResolverInterface::class);
        $routeMatcher = $this->make(RouteMatchMiddleware::class);
        $routeDispatcher = $this->make(RouteDispatcherMiddleware::class);

        if ($this->requestHandler instanceof RequestHandler) {
            $this->requestHandler->use($routeMatcher);
            $this->requestHandler->use($routeDispatcher);
        }

        return $this->requestHandler->handle($request);
    }
}

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
 *  @package    Platine\Framework\App
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\App;

use Platine\Config\Config;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Exception\HttpNotFoundException;
use Platine\Http\Handler\CallableResolverInterface;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Router;

/**
 * class Application
 * @package Platine\Framework\App
 */
class Application extends AbstractApplication implements RequestHandlerInterface
{

    /**
     * The router instance
     * @var Router
     */
    protected Router $router;

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
        parent::__construct($basePath);
        $this->loadConfiguredMiddlewares();
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
     * Add new middleware in the list
     * @param  MiddlewareInterface $middleware
     * @return self
     */
    public function addMiddlerware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;

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
        $handler = clone $this;
        if (key($handler->middlewares) === null) {
            throw new HttpNotFoundException($request);
        }

        $middleware = current($handler->middlewares);
        next($handler->middlewares);

        return $middleware->process($request, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $this->run();
    }

    /**
     * Set routing information
     * @return void
     */
    protected function setRouting(): void
    {
        $router = new Router();
        $router->setBasePath($this->basePath);

        $routes = $this->config->get('routes', []);
        //TODO find a way to remove return of array for
        //routes configuration
        $routes[0]($router);

        $this->router = $router;
        $this->instance($this->router);
    }

    /**
     * Load configured middlewares
     * @return void
     */
    protected function loadConfiguredMiddlewares(): void
    {
        /** @var Config $config */
        $config = $this->get(Config::class);

        /** @var string[] $middlewares */
        $middlewares = $config->get('middlewares', []);
        foreach ($middlewares as $middleware) {
            $this->use($middleware);
        }
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

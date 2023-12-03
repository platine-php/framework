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
 *  @file PaginationServiceProvider.php
 *
 *  The Framework pagination service provider class
 *
 *  @package    Platine\Framework\Service\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Config\Config;
use Platine\Container\ContainerInterface;
use Platine\Framework\Pagination\BootstrapRenderer;
use Platine\Framework\Pagination\ServerRequestUrlGenerator;
use Platine\Framework\Service\ServiceProvider;
use Platine\Http\ServerRequestInterface;
use Platine\Pagination\Pagination;
use Platine\Pagination\RendererInterface;
use Platine\Pagination\UrlGeneratorInterface;

/**
 * @class PaginationServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class PaginationServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(UrlGeneratorInterface::class, function (ContainerInterface $app) {
            return new ServerRequestUrlGenerator(
                $app->get(ServerRequestInterface::class),
                'page'
            );
        });

        $this->app->bind(RendererInterface::class, BootstrapRenderer::class);

        $this->app->bind(Pagination::class, function (ContainerInterface $app) {
            $pagination = new Pagination(
                $app->get(UrlGeneratorInterface::class),
                $app->get(RendererInterface::class)
            );

            /** @template T @var Config<T> $config */
            $config = $app->get(Config::class);

            $pagination->setItemsPerPage($config->get('pagination.item_per_page', 10))
                      ->setMaxPages($config->get('pagination.max_pages', 5));

            return $pagination;
        });
    }
}

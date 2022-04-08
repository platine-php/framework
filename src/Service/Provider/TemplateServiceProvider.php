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
 *  @file TemplateServiceProvider.php
 *
 *  The Framework template service provider class
 *
 *  @package    Platine\Framework\Service\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Config\Config;
use Platine\Container\ContainerInterface;
use Platine\Framework\Service\ServiceProvider;
use Platine\Template\Cache\AbstractCache;
use Platine\Template\Cache\NullCache;
use Platine\Template\Configuration;
use Platine\Template\Loader\FileLoader;
use Platine\Template\Loader\LoaderInterface;
use Platine\Template\Template;

/**
 * @class TemplateServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class TemplateServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $config = $this->app->get(Config::class)->get('template', []);

        $this->app->bind(Configuration::class, function (ContainerInterface $app) use ($config) {
            return new Configuration($config);
        });
        $this->app->bind(AbstractCache::class, $config['cache_driver'] ?? NullCache::class);
        $this->app->bind(LoaderInterface::class, FileLoader::class);
        $this->app->bind(Template::class);
    }
}

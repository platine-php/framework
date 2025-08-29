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

declare(strict_types=1);

namespace Platine\Framework\Helper;

use Platine\Config\Config;
use Platine\Framework\Auth\AuthorizationInterface;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Lang\Lang;
use Platine\Route\RouteCollectionInterface;
use Platine\Route\Router;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * @class Sidebar
 * @package Platine\Framework\Helper
 * @template T
 */
class Sidebar
{
    /**
     * The sidebar data
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected array $data = [];

    /**
     * Create new instance
     * @param Router $router
     * @param Lang $lang
     * @param CsrfManager $csrfManager
     * @param Config<T> $config
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        protected Router $router,
        protected Lang $lang,
        protected CsrfManager $csrfManager,
        protected Config $config,
        protected AuthorizationInterface $authorization
    ) {
    }

    /**
     * Add new sidebar
     * @param string $group
     * @param string $title
     * @param string $name
     * @param array<string, mixed> $params
     * @param array<string, mixed> $extras
     * @return $this
     */
    public function add(
        string $group,
        string $title,
        string $name,
        array $params = [],
        array $extras = []
    ): self {
        if (empty($group)) {
            $group = 'Actions';
        }

        if (!isset($this->data[$group])) {
            $this->data[$group] = [];
        }

        $this->data[$group][] = [
            'title' => $title,
            'name' => $name,
            'params' => $params,
            'extras' => $extras,
        ];

        return $this;
    }
    /**
     * Render the sidebar
     * @return string
     */
    public function render(): string
    {
        $str = '';

        /** @var RouteCollectionInterface $routes */
        $routes = $this->router->routes();
        $actionCount = 0;
        $color = $this->config->get('platform.sidebar_color_scheme', 'primary');

        foreach ($this->data as $group => $sidebar) {
            $str .= sprintf(
                '<div class="list-group page-sidebar">'
                    . '<a href="#" class="sidebar-action list-group-item list-group-item-%s"><b>%s</b></a>',
                $color,
                $group
            );

            foreach ($sidebar as $data) {
                $name = $data['name'];
                $route = $routes->get($name);
                $permission = $route->getAttribute('permission');
                if ($permission !== null && $this->authorization->isGranted($permission) === false) {
                    continue;
                }

                $actionCount++;

                $attributes = '';
                $query = '';
                $title = Str::ucfirst($data['title']);
                $csrf = (bool) $route->getAttribute('csrf');
                if ($csrf) {
                    if (!isset($data['extras']['query'])) {
                        $data['extras']['query'] = [];
                    }
                    $data['extras']['query'] = array_merge(
                        $data['extras']['query'],
                        $this->csrfManager->getTokenQuery()
                    );
                }

                if (count($data['extras']) > 0) {
                    $extras = $data['extras'];
                    if (isset($extras['confirm'])) {
                        $confirmMessage = $extras['confirm_message'] ??
                                $this->lang->tr('Do you want to proceed with this operation? [%s] ?', $title);
                        unset($extras['confirm'], $extras['confirm_message']);
                        $extras['data-text-confirm'] = $confirmMessage;
                    }
                    if (isset($extras['query'])) {
                        $query = Arr::query($extras['query']);
                        unset($extras['query']);
                    }
                    $attributes = Str::toAttribute($extras);
                }
                $url = sprintf(
                    '%s%s',
                    $this->router->getUri(
                        $data['name'],
                        $data['params']
                    ),
                    (!empty($query) ? '?' . $query : '')
                );

                $str .= sprintf(
                    '<a %s class="list-group-item list-group-item-action" href="%s">%s</a>',
                    $attributes,
                    $url,
                    $title
                );
            }
            $str .= '</div>';
        }

        if ($actionCount === 0) {
            return '';
        }

        return $str;
    }
}

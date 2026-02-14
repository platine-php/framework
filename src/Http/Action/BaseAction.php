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

namespace Platine\Framework\Http\Action;

use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Helper\Sidebar;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\ResponseInterface;
use Platine\Stdlib\Helper\Arr;
use Platine\Template\Template;

/**
 * @class BaseAction
 * @package Platine\Framework\Http\Action
 * @template T
 */
abstract class BaseAction extends BaseHttpAction
{
    /**
     * The name of the view
     * @var string
     */
    protected string $viewName = '';

    /**
     * The Sidebar instance
     * @var Sidebar
     */
    protected Sidebar $sidebar;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
    * The Flash instance
    * @var Flash
    */
    protected Flash $flash;


    /**
     * Create new instance
     * @param ActionHelper<T> $actionHelper
     */
    public function __construct(ActionHelper $actionHelper)
    {
        parent::__construct($actionHelper);
        $this->sidebar = $actionHelper->getSidebar();
        $this->template = $actionHelper->getTemplate();
        $this->flash = $actionHelper->getFlash();
    }

    /**
     * Set the view name
     * @param string $name
     * @return self<T>
     */
    public function setView(string $name): self
    {
        $this->viewName = $name;

        return $this;
    }

    /**
     * Add sidebar
     * @inheritDoc
     * @see Sidebar
     * @param array<string, mixed> $params
     * @param array<string, mixed> $extras
     * @return self<T>
     */
    public function addSidebar(
        string $group,
        string $title,
        string $name,
        array $params = [],
        array $extras = []
    ): self {
        $this->sidebar->add($group, $title, $name, $params, $extras);

        return $this;
    }

    /**
     * Return the template response
     * @return TemplateResponse
     */
    protected function viewResponse(): TemplateResponse
    {
        $sidebarContent = $this->sidebar->render();
        if (!empty($sidebarContent)) {
            $this->addContext('sidebar', $sidebarContent);
        }
        $this->addContext('pagination', $this->pagination->render());
        $this->addContext('app_url', $this->config->get('app.url'));
        $this->addContext('request_method', $this->request->getMethod());

        // Application info
        $this->addContext('app_name', $this->config->get('app.name'));
        $this->addContext('app_version', $this->config->get('app.version'));

        // Used in the footer
        $this->addContext('current_year', date('Y'));

        // Maintenance status
        $this->addContext('maintenance_state', app()->isInMaintenance());

        // Set nonce for Content Security Policy
        $nonces  = $this->request->getAttribute(SecurityPolicy::class);

        if ($nonces !== null) {
            $this->addContext('style_nonce', $nonces['nonces']['style']);
            $this->addContext('script_nonce', $nonces['nonces']['script']);
        }

        // get CSRF token if exist
        $csrfToken = $this->request->getAttribute('csrf_token');
        if ($csrfToken !== null) {
            $this->addContext('csrf_token', $csrfToken);
        }

        return new TemplateResponse(
            $this->template,
            $this->viewName,
            $this->context->all()
        );
    }

    /**
     * Redirect the user to another route
     * @param string $route
     * @param array<string, mixed> $params
     * @param array<string, mixed> $queries
     * @return RedirectResponse
     */
    protected function redirect(
        string $route,
        array $params = [],
        array $queries = []
    ): RedirectResponse {
        $queriesStr = null;
        if (count($queries) > 0) {
            $queriesStr = Arr::query($queries);
        }

        $routeUrl = $this->routeHelper->generateUrl($route, $params);
        if ($queriesStr !== null) {
            $routeUrl .= '?' . $queriesStr;
        }

        return new RedirectResponse($routeUrl);
    }

    /**
     * Redirect back to origin if user want to create new entity from
     * detail page
     * @return ResponseInterface|null
     */
    protected function redirectBackToOrigin(): ?ResponseInterface
    {
        $param = $this->param;
        $originId = (int) $param->get('origin_id', 0);
        $originRoute = $param->get('origin_route');

        if ($originRoute === null) {
            return null;
        }

        if ($originId === 0) {
            return $this->redirect($originRoute);
        }

        return $this->redirect($originRoute, ['id' => $originId]);
    }
}

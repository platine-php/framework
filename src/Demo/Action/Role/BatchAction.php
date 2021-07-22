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
 *  @file BatchAction.php
 *
 *  The Batch (delete, etc.) action class
 *
 *  @package    Platine\Framework\Demo\Action\Role
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Action\Role;

use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;

/**
 * @class BatchAction
 * @package Platine\Framework\Demo\Action\Role
 * @template T
 */
class BatchAction implements RequestHandlerInterface
{

    /**
     * Logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The role repository
     * @var RoleRepository
     */
    protected RoleRepository $roleRepository;

    /**
     * The route helper instance
     * @var RouteHelper
     */
    protected RouteHelper $routeHelper;

    /**
     * The session instance
     * @var Session
     */
    protected Session $session;

    /**
     * The translator instance
     * @var Lang
     */
    protected Lang $lang;

    /**
     * The server request instance
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The items to handle batch actions
     * @var array<mixed>
     */
    protected array $items = [];

    /**
     * Create new instance
     * @param Lang $lang
     * @param Session $session
     * @param LoggerInterface $logger
     * @param RoleRepository $roleRepository
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        Lang $lang,
        Session $session,
        LoggerInterface $logger,
        RoleRepository $roleRepository,
        RouteHelper $routeHelper
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->session = $session;
        $this->roleRepository = $roleRepository;
        $this->routeHelper = $routeHelper;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $param = new RequestData($request);

        $items =  $param->post('items', []);
        if (empty($items)) {
            return new RedirectResponse(
                $this->routeHelper->generateUrl('role_list')
            );
        }

        $this->request = $request;
        $this->items = $items;

        $actions = [
            'delete',
        ];

        foreach ($actions as $action) {
            if ($param->post($action)) {
                $method = $action . 'Handle';

                $result = $this->{$method}();
                //If the return if the response just return it
                if ($result instanceof ResponseInterface) {
                    return $result;
                }
                break;
            }
        }

        return new RedirectResponse(
            $this->routeHelper->generateUrl('role_list')
        );
    }

    /**
     * Handle delete action
     * @return mixed|void
     */
    protected function deleteHandle()
    {
        $items = $this->items;
        $this->logger->info('Deleted of roles #{items}', ['items' => $items]);

        $this->roleRepository->query()
                            ->where('id')
                            ->in($items)
                            ->delete();

        $this->session->setFlash('success', 'The selected roles are deleted successfully');
    }
}

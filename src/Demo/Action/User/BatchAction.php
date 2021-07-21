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
 *  @package    Platine\Framework\Demo\Action\User
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * @class BatchAction
 * @package Platine\Framework\Demo\Action\User
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
     * The user repository
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

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
     * @param Template $template
     * @param UserRepository $userRepository
     * @param RouteHelper $routeHelper
     */
    public function __construct(
        Lang $lang,
        Session $session,
        LoggerInterface $logger,
        Template $template,
        UserRepository $userRepository,
        RouteHelper $routeHelper
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->template = $template;
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
                $this->routeHelper->generateUrl('user_list')
            );
        }

        $this->request = $request;
        $this->items = $items;

        $actions = [
            'delete',
            'disable',
            'enable',
        ];

        foreach ($actions as $action) {
            if ($param->post($action)) {
                $method = $action . 'Handle';

                $this->{$method}();
                break;
            }
        }

        return new RedirectResponse(
            $this->routeHelper->generateUrl('user_list')
        );
    }

    /**
     * Handle delete action
     * @return void
     */
    protected function deleteHandle(): void
    {
        $items = $this->items;
        $this->logger->info('Deleted of user #{items}', ['items' => $items]);

        $this->userRepository->query()
                            ->where('id')
                            ->in($items)
                            ->delete();

        $this->session->setFlash('success', 'The selected users are deleted successfully');
    }

    /**
     * Handle disable action
     * @return void
     */
    protected function disableHandle(): void
    {
        $items = $this->items;
        $this->logger->info('Disable of user #{items}', ['items' => $items]);

        $this->userRepository->query()
                            ->where('id')
                            ->in($items)
                            ->update(['status' => false]);

        $this->session->setFlash('success', 'The selected users are disabled successfully');
    }

    /**
     * Handle enable action
     * @return void
     */
    protected function enableHandle(): void
    {
        $items = $this->items;
        $this->logger->info('Enable of user #{items}', ['items' => $items]);

        $this->userRepository->query()
                            ->where('id')
                            ->in($items)
                            ->update(['status' => true]);

        $this->session->setFlash('success', 'The selected users are enabled successfully');
    }
}

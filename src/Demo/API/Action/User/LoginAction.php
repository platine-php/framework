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
 *  @file LoginAction.php
 *
 *  The Login action class
 *
 *  @package    Platine\Framework\Demo\API\Action\User
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\API\Action\User;

use Platine\Framework\Auth\ApiAuthenticationInterface;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\AuthenticationException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Demo\API\Param\LoginParam;
use Platine\Framework\Demo\API\Validator\LoginValidator;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;

/**
 * @class LoginAction
 * @package Platine\Framework\Demo\API\Action\User
 * @template T
 */
class LoginAction implements RequestHandlerInterface
{

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The translator instance
     * @var Lang
     */
    protected Lang $lang;

    /**
     * The authentication instance
     * @var ApiAuthenticationInterface
     */
    protected ApiAuthenticationInterface $authentication;

    /**
     * Create new instance
     * @param Lang $lang
     * @param ApiAuthenticationInterface $authentication
     * @param LoggerInterface $logger
     */
    public function __construct(
        Lang $lang,
        ApiAuthenticationInterface $authentication,
        LoggerInterface $logger
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->authentication = $authentication;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $param = new RequestData($request);
        $formParam = new LoginParam($param->posts());
        $validator = new LoginValidator($formParam, $this->lang);

        if (!$validator->validate()) {
            return new RestResponse(
                [],
                ['errors' => $validator->getErrors()],
                false,
                4000,
                'Missing username/password',
                400
            );
        }

        $username = $param->post('username');
        $password = $param->post('password');

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        $data = [];

        try {
            $data = $this->authentication->login($credentials);
        } catch (
            MissingCredentialsException
                | AccountNotFoundException
                | InvalidCredentialsException
                | AccountLockedException $ex
        ) {
            return $this->getExceptionResponse($ex, $formParam);
        }

        return new RestResponse(
            $data
        );
    }

    /**
     * Return response when authentication throw Exception
     * @param AuthenticationException $ex
     * @param LoginParam $formParam
     * @return ResponseInterface
     */
    protected function getExceptionResponse(
        AuthenticationException $ex,
        LoginParam $formParam
    ): ResponseInterface {
        $this->logger->error('Authentication Error', ['exception' => $ex]);
        return new RestResponse(
            [],
            [],
            false,
            4001,
            $ex->getMessage(),
            400
        );
    }
}

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
 *  @file AuthenticationInterface.php
 *
 *  The Authentication interface
 *
 *  @package    Platine\Framework\Auth
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Auth;

use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;

/**
 * class AuthenticationInterface
 * @package Platine\Framework\Auth
 */
interface AuthenticationInterface
{

    /**
     * Authenticate the user
     * @param array<string, mixed> $credentials
     * @param bool $remeberMe
     * @return bool
     *
     * @throws MissingCredentialsException if the passed credentials is not correct
     * @throws AccountNotFoundException if can not find the account information
     * @throws InvalidCredentialsException if invalid credentials, like wrong password
     * @throws AccountLockedException if account is locked
     */
    public function login(array $credentials = [], bool $remeberMe = false): bool;

    /**
     * Check if user is logged
     * @return bool
     */
    public function isLogged(): bool;

    /**
     * Logout the user
     * @return void
     */
    public function logout(): void;

    /**
     * Return the current logged user
     * @return IdentityInterface
     *
     * @throws AccountNotFoundException if can not find the account information
     */
    public function getUser(): IdentityInterface;
}

<?php

/**
 * Platine PHP
 *
 * Platine PHP is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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
 *  @file UserAuthentication.php
 *
 *  The User Authentication class
 *
 *  @package    Platine\Framework\OAuth2\User
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\OAuth2\User;

use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Exception\AuthenticationException;
use Platine\OAuth2\Entity\TokenOwnerInterface;
use Platine\OAuth2\Entity\UserAuthenticationInterface;

/**
 * @class UserAuthentication
 * @package Platine\Framework\OAuth2\User
 */
class UserAuthentication implements UserAuthenticationInterface
{
    /**
     * Create new instance
     * @param AuthenticationInterface $authentication
     */
    public function __construct(protected AuthenticationInterface $authentication)
    {
    }


    /**
     * {@inheritdoc}
     */
    public function validate(string $username, string $password): ?TokenOwnerInterface
    {
        $userId = null;
        try {
            $isLogged = $this->authentication->login([
                'username' => $username,
                'password' => $password,
            ]);

            if ($isLogged) {
                $userId = $this->authentication->getUser()->getId();
            }
        } catch (AuthenticationException $ex) {
            return null;
        }

        if ($userId === null) {
            return null;
        }

        return new TokenOwner($userId);
    }
}

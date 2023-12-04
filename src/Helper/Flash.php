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
 *  @file Flash.php
 *
 *  The Flash message management class
 *
 *  @package    Platine\Framework\Helper
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Helper;

use Platine\Session\Session;

/**
 * @class Flash
 * @package Platine\Framework\Helper
 */
class Flash
{
    /**
     * The session instance
     * @var Session
     */
    protected Session $session;

    /**
     * Create new instance
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Set flash error
     * @param string $message
     * @return $this
     */
    public function setError(string $message): self
    {
        $this->session->setFlash('error', $message);

        return $this;
    }

    /**
     * Set flash success
     * @param string $message
     * @return $this
     */
    public function setSuccess(string $message): self
    {
        $this->session->setFlash('success', $message);

        return $this;
    }

    /**
     * Set flash information
     * @param string $message
     * @return $this
     */
    public function setInfo(string $message): self
    {
        $this->session->setFlash('info', $message);

        return $this;
    }

    /**
     * Set flash warning
     * @param string $message
     * @return $this
     */
    public function setWarning(string $message): self
    {
        $this->session->setFlash('warning', $message);

        return $this;
    }

    /**
     * Return the success flash
     * @return string|null
     */
    public function getSuccess(): ?string
    {
        return $this->session->getFlash('success');
    }

    /**
     * Return the error flash
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->session->getFlash('error');
    }

    /**
     * Return the info flash
     * @return string|null
     */
    public function getInfo(): ?string
    {
        return $this->session->getFlash('info');
    }

    /**
     * Return the warning flash
     * @return string|null
     */
    public function getWarning(): ?string
    {
        return $this->session->getFlash('warning');
    }
}

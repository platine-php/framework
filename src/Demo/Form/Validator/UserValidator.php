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
 *  @file UserValidator.php
 *
 *  The user validation class
 *
 *  @package    Platine\Framework\Demo\Form\Validator
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Form\Validator;

use Platine\Framework\Demo\Form\Param\UserParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Validator\Rule\AlphaDash;
use Platine\Validator\Rule\Email;
use Platine\Validator\Rule\InList;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;
use Platine\Validator\Validator;

/**
 * @class UserValidator
 * @package Platine\Framework\Demo\Form\Validator
 */
class UserValidator extends AbstractValidator
{

    /**
     * The user parameter instance
     * @var UserParam
     */
    protected UserParam $param;

    /**
     * Create new instance
     * @param UserParam $param
     * @param Validator|null $validator
     */
    public function __construct(UserParam $param, ?Validator $validator = null)
    {
        parent::__construct($validator);
        $this->param = $param;
    }

    /**
     * {@inheritodc}
     */
    public function setData(): void
    {
        $this->addData('username', $this->param->getUsername());
        $this->addData('lastname', $this->param->getLastname());
        $this->addData('firstname', $this->param->getFirstname());
        $this->addData('role', $this->param->getRole());
        $this->addData('email', $this->param->getEmail());
        $this->addData('status', $this->param->getStatus());
        $this->addData('password', $this->param->getPassword());
    }

    /**
     * {@inheritodc}
     */
    public function setRules(): void
    {
        $this->validator->addRules('username', [
           new NotEmpty(),
           new MinLength(3),
           new AlphaDash()
        ]);

        $this->validator->addRules('lastname', [
           new NotEmpty(),
           new MinLength(3)
        ]);

        $this->validator->addRules('firstname', [
           new NotEmpty(),
           new MinLength(3)
        ]);

        $this->validator->addRules('status', [
           new NotEmpty(),
           new InList([0, 1])
        ]);

        $this->validator->addRules('email', [
           new NotEmpty(),
           new Email()
        ]);

        $this->validator->addRules('role', [
           new MinLength(3)
        ]);

        $this->validator->addRules('password', [
           new MinLength(5),
        ]);
    }
}

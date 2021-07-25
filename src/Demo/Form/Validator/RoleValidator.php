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
 *  @file RoleValidator.php
 *
 *  The role validation class
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

use Platine\Framework\Demo\Form\Param\RoleParam;
use Platine\Framework\Form\Validator\AbstractValidator;
use Platine\Lang\Lang;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;

/**
 * @class RoleValidator
 * @package Platine\Framework\Demo\Form\Validator
 */
class RoleValidator extends AbstractValidator
{

    /**
     * The parameter instance
     * @var RoleParam
     */
    protected RoleParam $param;

    /**
     * Create new instance
     * @param RoleParam $param
     * @param Lang $lang
     */
    public function __construct(RoleParam $param, Lang $lang)
    {
        parent::__construct($lang);
        $this->param = $param;
    }

    /**
     * {@inheritodc}
     */
    public function setValidationData(): void
    {
        $this->addData('name', $this->param->getName());
        $this->addData('description', $this->param->getDescription());
    }

    /**
     * {@inheritodc}
     */
    public function setValidationRules(): void
    {
        $this->addRules('name', [
           new NotEmpty(),
           new MinLength(2)
        ]);

        $this->addRules('description', [
           new MinLength(3)
        ]);
    }
}

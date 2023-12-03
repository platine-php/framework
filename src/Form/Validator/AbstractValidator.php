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
 *  @file AbstractValidator.php
 *
 *  The Validator base class
 *
 *  @package    Platine\Framework\Form\Validator
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Form\Validator;

use Platine\Lang\Lang;
use Platine\Validator\Validator;

/**
 * @class AbstractValidator
 * @package Platine\Framework\Form\Validator
 */
abstract class AbstractValidator extends Validator
{
    /**
     * Create new instance
     * @param Lang $lang
     */
    public function __construct(Lang $lang)
    {
        parent::__construct($lang);
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function validate(array $data = []): bool
    {
        $this->setValidationRules();
        $this->setValidationData();

        return parent::validate($data);
    }

    /**
     * Add validation data
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addData(string $name, $value): self
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Set the validation rules
     * @return void
     */
    abstract public function setValidationRules(): void;

    /**
     * Set the validation data
     * @return void
     */
    abstract public function setValidationData(): void;
}

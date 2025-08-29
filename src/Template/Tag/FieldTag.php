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

namespace Platine\Framework\Template\Tag;

use Platine\Framework\Form\Param\BaseParam;
use Platine\Lang\Lang;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
* @class FieldTag
* @package Platine\Framework\Template\Tag
*/
class FieldTag extends AbstractTag
{
    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        if ($lexer->match($markup)) {
            $this->extractAttributes($markup);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: field [PARAMETERS ...]',
                'field'
            ));
        }
    }

    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        $attributes = $this->normalizeAttributes($context);
        if (count($attributes) === 0) {
            return '';
        }

        $name = $attributes['name'];
        $type = $attributes['type'];
        $errorText = $this->getError($context, $name);
        $label = Str::ucfirst($attributes['label']);
        $width = $attributes['width'];
        unset($attributes['label'], $attributes['width']);
        [$labelWidth, $inputWidth] = $this->getWidthInfo($width);
        $required = (bool) $attributes['required'];

        $str = sprintf('<div class="form-group row%s">', $type === 'textarea' ? ' mb-2' : '');
        $str .= $this->buildLabel($label, $name, $required, $labelWidth);
        $str .= $this->buildField($type, $inputWidth, $attributes, $errorText);
        $str .= '</div>';

        return $str;
    }

    /**
     * Return the error text
     * @param Context $context
     * @param string $name
     * @return string|null
     */
    protected function getError(Context $context, string $name): ?string
    {
        $errors = $context->get('errors');
        if ($errors === null) {
            $errors = [];
        }
        $errorText = $errors[$name] ?? null;

        return $errorText;
    }


    /**
     * Normalize the attributes
     * @param Context $context
     * @return array<string, mixed>
     */
    protected function normalizeAttributes(Context $context): array
    {
        $parameters = [];
        foreach ($this->attributes as $key => $value) {
            if ($context->hasKey($value)) {
                $value = $context->get($value);
                if (is_scalar($value)) {
                    $value = (string) $value;
                }
            }

            $parameters[$key] = $value;
        }

        $attributes = array_merge(
            [
                'label' => '',
                'width' => '3-9',
                'type' => 'text',
                'class' => 'form-control form-control-sm',
                'required' => false,
            ],
            $parameters
        );
        $name = $attributes['name'] ?? null;
        if ($name === null) {
            return [];
        }

        $label = Str::ucfirst($attributes['label']);
        $lang = app(Lang::class);

        if (array_key_exists('placeholder', $attributes) === false) {
            $attributes['placeholder'] = $lang->tr('Please fill the field %s', Str::lower($label));
        }

        if (array_key_exists('value', $attributes) === false) {
            $param = $context->get('param');
            if ($param instanceof BaseParam) {
                $value = $param->{$name};
                $attributes['value'] = (string) $value;
            }
        }

        if (array_key_exists('id', $attributes) === false) {
            $attributes['id'] = $name;
        }

        return $attributes;
    }




    /**
     * Return the information width of field
     * @param string $width
     * @return array<int>
     */
    protected function getWidthInfo(string $width): array
    {
        $labelWidth = 3;
        $inputWidth = 9;
        $arr = explode('-', $width, 2);
        if (count($arr) < 2) {
            $inputWidth = (int) $arr[0];
            $labelWidth = 12 - $inputWidth;
        } else {
            $inputWidth = (int) $arr[1];
            $labelWidth = (int) $arr[0];
        }

        return [$labelWidth, $inputWidth];
    }

    /**
     * Build label
     * @param string $label
     * @param string $name
     * @param bool $required
     * @param int $labelWidth
     * @return string
     */
    protected function buildLabel(
        string $label,
        string $name,
        bool $required,
        int $labelWidth
    ): string {
        if (!empty($label)) {
            $label .= ':';
        }

        $str = sprintf(
            '<label for="%s" class="col-md-%d col-form-label">%s ',
            $name,
            $labelWidth,
            $label
        );

        if (!empty($label) && $required) {
            $str .= '<span class="required">*</span>';
        }

        $str .= '</label>';

        return $str;
    }

    /**
     * Build input
     * @param string $type
     * @param int $inputWidth
     * @param array<string, mixed> $attributes
     * @param string|null $errorText
     * @return string
     */
    protected function buildField(
        string $type,
        int $inputWidth,
        array $attributes,
        ?string $errorText
    ): string {
        $value = null;
        if ($type === 'textarea') {
            $value = $attributes['value'] ?? '';
            unset($attributes['type'], $attributes['value']);
        }

        $required = (bool) $attributes['required'];
        if ($required === false) {
            unset($attributes['required']);
        }

        $str = sprintf(
            '<div class="col-md-%d"><%s %s>',
            $inputWidth,
            $type === 'textarea' ? 'textarea' : 'input',
            Str::toAttribute($attributes)
        );

        if ($type === 'textarea') {
            $str .= sprintf('%s</textarea>', $value);
        }

        if ($errorText !== null) {
            $str .= sprintf('<span class="ferror">%s</span>', $errorText);
        }

        $str .= '</div>';

        return $str;
    }
}

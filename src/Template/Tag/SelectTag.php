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

use Platine\Lang\Lang;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
* @class SelectTag
* @package Platine\Framework\Template\Tag
*/
class SelectTag extends FieldTag
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
                'Syntax Error in "%s" - Valid syntax: select [PARAMETERS ...]',
                'select'
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
        $errorText = $this->getError($context, $name);
        $label = Str::ucfirst($attributes['label']);
        $width = $attributes['width'];
        unset($attributes['label'], $attributes['width'], $attributes['type']);
        [$labelWidth, $inputWidth] = $this->getWidthInfo($width);
        $required = (bool) $attributes['required'];

        $str = '<div class="form-group row">';
        $str .= $this->buildLabel($label, $name, $required, $labelWidth);
        $str .= $this->buildSelect($inputWidth, $attributes, $errorText, $required);
        $str .= '</div>';

        return $str;
    }

    /**
     * Build select form
     * @param int $inputWidth
     * @param array<string, mixed> $attributes
     * @param string|null $errorText
     * @param bool $required
     * @return string
     */
    protected function buildSelect(
        int $inputWidth,
        array $attributes,
        ?string $errorText,
        bool $required
    ): string {
        $value = $attributes['value'];
        $isEnum = (bool) ($attributes['enum'] ?? false);
        $data = $attributes['data'] ?? [];
        if (is_array($data) === false) {
            $data = [];
        }

        $separator = $attributes['separator'] ?? ' ';
        $fieldId = 'id';
        $fieldDisplay = 'name';
        if (array_key_exists('key', $attributes)) {
            $fieldId = $attributes['key'];
        }

        if (array_key_exists('display', $attributes)) {
            $fieldDisplay = $attributes['display'];
        }

        if ($required === false) {
            unset($attributes['required']);
        }

        unset(
            $attributes['value'],
            $attributes['data'],
            $attributes['display'],
            $attributes['enum'],
            $attributes['separator'],
            $attributes['key']
        );
        if (Str::contains('select2js', $attributes['class']) === false) {
            $attributes['class'] .= ' select2js';
        }

        $str = sprintf(
            '<div class="col-md-%d"><select %s>',
            $inputWidth,
            Str::toAttribute($attributes)
        );

        $options = '';
        $isArray = isset($data[0]) && is_array($data[0]);
        if ($required === false) {
            $lang = app(Lang::class);
            $options .= sprintf('<option value="">-- %s --</option>', $lang->tr('Please select'));
        }

        $displayFields = explode(';', $fieldDisplay);
        $display = array_map('trim', $displayFields);
        foreach ($data as $key => $row) {
            $id = $key;
            $optionName = $row;
            if ($isEnum === false) {
                $id = $isArray ? $row[$fieldId] : $row->{$fieldId};
                $optionName = '';

                foreach ($display as $v) {
                    $optionValue = $isArray ? $row[$v] : $row->{$v};
                    $optionName .= sprintf(
                        '%s%s',
                        $optionValue,
                        $separator
                    );
                }
            }

            $options .= sprintf(
                '<option value="%s"%s>%s</option>',
                $id,
                $value == $id ? ' selected' : '', // don't use === here
                rtrim($optionName, $separator)
            );
        }

        $str .= sprintf('%s</select>', $options);

        if ($errorText !== null) {
            $str .= sprintf('<span class="ferror">%s</span>', $errorText);
        }

        $str .= '</div>';

        return $str;
    }
}

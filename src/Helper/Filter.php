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

namespace Platine\Framework\Helper;

use Platine\Framework\Enum\FilterFieldType;
use Platine\Lang\Lang;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * @class Filter
 * @package Platine\Framework\Helper
 */
class Filter
{
    /**
     * The filter fields
     * @var array<string, array<string, mixed>>
     */
    protected array $fields = [];

    /**
     * The queries parameters to be used
     * @var array<string, mixed>
     */
    protected array $params = [];

    /**
     * Ignore other filters if search filter is used
     * @var bool
     */
    protected bool $keepOnlySearchFilter = true;

    /**
     * Keep these fields if search filter is used
     * @var array<string>
     */
    protected array $searchFilterKeepFields = [];

    /**
     * The attributes to be used
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Whether to call configure method in constructor
     * @var bool
     */
    protected bool $autoConfigure = true;

    /**
     * Create new instance
     * @param Lang $lang
     */
    public function __construct(protected Lang $lang)
    {
        if ($this->autoConfigure) {
            $this->configure();
        }
    }

    /**
     * Return the attributes
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the attributes
     * @param array<string, mixed> $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Set one attribute
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }


    /**
     *
     * @param bool $keepOnlySearchFilter
     * @return $this
     */
    public function setKeepOnlySearchFilter(bool $keepOnlySearchFilter): self
    {
        $this->keepOnlySearchFilter = $keepOnlySearchFilter;
        return $this;
    }

    /**
     *
     * @param array<string> $searchFilterKeepFields
     * @return $this
     */
    public function setSearchFilterKeepFields(array $searchFilterKeepFields): self
    {
        $this->searchFilterKeepFields = $searchFilterKeepFields;
        return $this;
    }


    /**
     * Return the queries parameters
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        $params = $this->params;
        if ($this->keepOnlySearchFilter === false) {
            return $params;
        }

        if (array_key_exists('search', $params)) {
            $params = Arr::only($params, [...$this->searchFilterKeepFields, 'search']);

            return $params;
        }

        return $params;
    }

    /**
     * Set the queries parameters
     * @param array<string, mixed> $params
     * @return $this
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Return parameter value or null
     * @param string $param
     * @return mixed
     */
    public function getParam(string $param): mixed
    {
        return $this->params[$param] ?? null;
    }

    /**
     * Add select field
     * @param string $field
     * @param string $title
     * @param array<mixed> $values
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    public function addSelectField(
        string $field,
        string $title,
        array $values,
        string $default = '',
        array $extras = []
    ): self {
        return $this->addListField(
            $field,
            $title,
            FilterFieldType::SELECT,
            $values,
            $default,
            $extras
        );
    }

    /**
     * Add text field
     * @param string $field
     * @param string $title
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    public function addTextField(
        string $field,
        string $title,
        string $default = '',
        array $extras = []
    ): self {
        $this->addCommonField(
            $field,
            $title,
            FilterFieldType::TEXT,
            $default,
            $extras
        );

        return $this;
    }

    /**
     * Add date field
     * @param string $field
     * @param string $title
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    public function addDateField(
        string $field,
        string $title,
        string $default = '',
        array $extras = []
    ): self {
        $this->addCommonField(
            $field,
            $title,
            FilterFieldType::DATE,
            $default,
            $extras
        );

        return $this;
    }

    /**
     * Add hidden field
     * @param string $field
     * @param string $title
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    public function addHiddenField(
        string $field,
        string $title,
        string $default = '',
        array $extras = []
    ): self {
        $this->addCommonField(
            $field,
            $title,
            FilterFieldType::HIDDEN,
            $default,
            $extras
        );

        return $this;
    }

    /**
     * Configure the filter
     * @return $this
     */
    public function configure(): self
    {
        return $this;
    }

    /**
     * Return the filter fields
     * @return array<string, array<string, mixed>>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Render the filter
     * @return string
     */
    public function render(): string
    {
        if (count($this->fields) === 0) {
            return '';
        }

        $attributeStr = '';
        if (count($this->attributes) > 0) {
            $attributeStr = Str::toAttribute($this->attributes);
        }

        $str = '<div class="text-right">';
        $filterCollapseBtnText = $this->lang->tr('Filter collapse button text');
        $filterBtnText = $this->lang->tr('Filter button text');
        $str .= sprintf(
            '<div>'
            . '<button data-bs-toggle="collapse" data-bs-target=".app-form-filter" '
            . 'class="btn btn-sm btn-primary">'
            . '<i class="fa fa-filter"></i> %s</button></div><br />',
            $filterCollapseBtnText
        );

        $str .= sprintf(
            '<form action="" method="GET" %s data-form-type="filter" class="collapse app-form-filter">',
            $attributeStr
        );

        $methodMaps = $this->getMethodMaps();

        foreach ($this->fields as $field => $data) {
            $renderMethodName = $methodMaps[$data['type']];
            $render = $this->{$renderMethodName}($field) . ' &nbsp;&nbsp;';
            $str .= $render;
        }
        $str .= sprintf(
            '<button type="submit" class="btn btn-xs btn-primary">%s</button></form><br /></div>',
            $filterBtnText
        );

        return $str;
    }

    /**
     * Render filter for form
     * @return string
     */
    public function form(): string
    {
        if (count($this->fields) === 0) {
            return '';
        }

        $attributeStr = '';
        if (count($this->attributes) > 0) {
            $attributeStr = Str::toAttribute($this->attributes);
        }

        $str = '';
        $methodMaps = $this->getMethodMaps();
        foreach ($this->fields as $field => $data) {
            $renderMethodName = $methodMaps[$data['type']];
            $render = $this->{$renderMethodName}($field, true);
            $str .= $render;
        }

        return $str;
    }

    /**
     * Return the method maps
     * @return array<string, string>
     */
    protected function getMethodMaps(): array
    {
        return [
            FilterFieldType::DATE => 'renderDate',
            FilterFieldType::TEXT => 'renderText',
            FilterFieldType::SELECT => 'renderSelect',
            FilterFieldType::HIDDEN => 'renderHidden',
        ];
    }

    /**
     * Add common field
     * @param string $field
     * @param string $title
     * @param string $type
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    protected function addCommonField(
        string $field,
        string $title,
        string $type,
        string $default = '',
        array $extras = []
    ): self {
        $this->fields[$field] = [
          'type' => $type,
          'title' => $title,
          'value' => $default,
          'extras' => $extras,
        ];

        return $this;
    }

    /**
     * Add list field
     * @param string $field
     * @param string $title
     * @param string $type
     * @param array<mixed> $values
     * @param string $default
     * @param array<string, mixed> $extras
     * @return $this
     */
    protected function addListField(
        string $field,
        string $title,
        string $type,
        array $values = [],
        string $default = '',
        array $extras = []
    ): self {
        $this->fields[$field] = [
          'type' => $type,
          'title' => $title,
          'values' => $values,
          'value' => $default,
          'extras' => $extras,
        ];

        return $this;
    }

    /**
     * Render for text field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderText(string $field, bool $isForm = false): string
    {
        return $this->renderTextField($field, 'text', $isForm);
    }

    /**
     * Render for hidden field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderHidden(string $field, bool $isForm = false): string
    {
        return $this->renderTextField($field, 'hidden', $isForm);
    }

    /**
     * Render for select field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderSelect(string $field, bool $isForm = false): string
    {
        $data = $this->fields[$field] ?? [];
        $values = $data['values'] ?? [];
        if (count($values) === 0) {
            return '';
        }

        if ($isForm) {
            $str = '<div class="form-group row">';
        } else {
            $str = '';
        }
        $title = $data['title'] ?? '';
        $label = str_replace(['[', ']'], '', $field);
        if (!empty($title)) {
            if ($isForm) {
                $str .= sprintf('<label for="%s" class="col-md-3 col-form-label">%s:</label>', $label, $title);
            } else {
                $str .= sprintf('<label for="%s">%s:</label> &nbsp;&nbsp;', $label, $title);
            }
        } else {
            if ($isForm) {
                $str .= sprintf('<label for="%s" class="col-md-3 col-form-label">&nbsp;</label>', $label);
            }
        }

        $extras = $data['extras'];
        $default = $data['value'];
        $keyField = $extras['key_field'] ?? 'id';
        $newLine = $extras['new_line'] ?? false;
        $formatFunction = $extras['format_function'] ?? null;
        unset(
            $extras['key_field'],
            $extras['format_function'],
            $extras['new_line']
        );
        $attributes = $extras;
        $attributes['name'] = $field;
        $attributes['id'] = $label;

        if (isset($attributes['required']) && !$attributes['required']) {
            unset($attributes['required']);
        }

        if ($isForm) {
            if (!isset($attributes['class'])) {
                $attributes['class'] = 'form-control form-control-sm';
            } else {
                if (strpos($attributes['class'], 'form-control') === false) {
                    $attributes['class'] .= ' form-control';
                }

                if (strpos($attributes['class'], 'form-control-sm') === false) {
                    $attributes['class'] .= ' form-control-sm';
                }
            }
        }

        $normalizedField = str_replace(['[', ']'], '', $field);
        $value = $this->params[$normalizedField] ?? $default;
        if (!is_array($value)) {
            $value = (string) $value;
        }

        if ($isForm) {
            $colWidth = 9;
            $str .= sprintf('<div class="col-md-%d">', $colWidth);
        }
        $str .= sprintf('<select %s>', Str::toAttribute($attributes));
        if (!isset($attributes['required'])) {
            $str .= sprintf('<option value="">-- %s --</option>', $this->lang->tr('Please select'));
        }
        $isIndexed = Arr::isIndexed($values);
        foreach ($values as $key => $option) {
            if ($isIndexed && is_object($option) === false) {
                $key = $option;
            }

            if (is_numeric($key) && is_object($option)) {
                $key = $option->{$keyField};
                if ($formatFunction !== null) {
                    $option = call_user_func_array($formatFunction, [$option]);
                } else {
                    $option = Str::stringify($option);
                }
            }
            $selected = (is_string($value) && $key == $value) || (is_array($value) && in_array($key, $value));
            $str .= sprintf('<option value="%s" %s>%s</option>', $key, $selected ? 'selected' : '', $option);
        }
        $str .= '</select>';
        if ($isForm) {
            $str .= '</div>';
        }

        if ($newLine) {
            $str .= '<br /><br />';
        }
        if ($isForm) {
            $str .= '</div>';
        }

        return $str;
    }

    /**
     * Render for date field
     * @param string $field
     * @param bool $isForm
     * @return string
     */
    protected function renderDate(string $field, bool $isForm = false): string
    {
        return $this->renderTextField($field, 'date', $isForm);
    }

    /**
     * Render common text field
     * @param string $field
     * @param string $type
     * @param bool $isForm
     * @return string
     */
    protected function renderTextField(string $field, string $type, bool $isForm = false): string
    {
        $data = $this->fields[$field] ?? [];
        if ($isForm) {
            $str = '<div class="form-group row">';
        } else {
            $str = '';
        }
        $title = $data['title'] ?? '';
        $label = str_replace(['[', ']'], '', $field);
        if (!empty($title)) {
            if ($isForm) {
                $str .= sprintf('<label for="%s" class="col-md-3 col-form-label">%s:</label>', $label, $title);
            } else {
                $str .= sprintf('<label for="%s">%s:</label> &nbsp;&nbsp;', $label, $title);
            }
        } else {
            if ($isForm) {
                $str .= sprintf('<label for="%s" class="col-md-3 col-form-label">&nbsp;</label>', $label);
            }
        }

        $extras = $data['extras'];
        $newLine = $extras['new_line'] ?? false;
        unset($extras['new_line']);

        $attributes = $extras;
        $default = $data['value'];
        $attributes['name'] = $field;
        $attributes['type'] = $type;
        $attributes['id'] = $label;

        if (isset($attributes['required']) && !$attributes['required']) {
            unset($attributes['required']);
        }

        if ($isForm) {
            if (!isset($attributes['class'])) {
                $attributes['class'] = 'form-control form-control-sm';
            } else {
                if (strpos($attributes['class'], 'form-control') === false) {
                    $attributes['class'] .= ' form-control';
                }

                if (strpos($attributes['class'], 'form-control-sm') === false) {
                    $attributes['class'] .= ' form-control-sm';
                }
            }
        }

        $value = $this->params[$field] ?? $default;
        $attributes['value'] = $value;

        if (!empty($attributes['value']) && $type === 'date') {
            $attributes['value'] = date('Y-m-d', strtotime($attributes['value']));
        }

        if ($isForm) {
            $colWidth = 9;
            $str .= sprintf('<div class="col-md-%d">', $colWidth);
        }

        $str .= sprintf('<input %s />', Str::toAttribute($attributes));
        if ($newLine) {
            $str .= '<br /><br />';
        }
        if ($isForm) {
            $str .= '</div>';
            $str .= '</div>';
        }

        return $str;
    }
}

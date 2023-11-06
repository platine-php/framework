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
 *  @file BaseParam.php
 *
 *  The Form base parameter class
 *
 *  @package    Platine\Framework\Form\Param
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Form\Param;

use JsonSerializable;
use Platine\Orm\Entity;
use Platine\Stdlib\Helper\Str;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * @class BaseParam
 * @package Platine\Framework\Form\Param
 * @template TEntity as Entity
 */
class BaseParam implements JsonSerializable
{
    /**
     * Create new instance
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $params = array_merge($this->getDefault(), $data);
        $this->load($params);
    }

    /**
     * Load the field data
     * @param array<string, mixed> $data
     * @return void
     */
    public function load(array $data): void
    {
        foreach ($data as $name => $value) {
            $key = Str::camel($name, true);
            $typedValue = $this->getValue($key, $value);

            $setterMethod = 'set' . ucfirst($key);
            if (method_exists($this, $setterMethod)) {
                $this->{$setterMethod}($typedValue);
            } elseif (property_exists($this, $key)) {
                $this->{$key} = $typedValue;
            }
        }
    }

    /**
     *
     * @param TEntity $entity
     * @return $this
     */
    public function fromEntity(Entity $entity): self
    {
        return $this;
    }

    /**
     * Return the fields default values
     * @return array<string, mixed>
     */
    public function getDefault(): array
    {
        return [];
    }

    /**
     * Return the parameters values
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return get_object_vars($this);
    }

    /**
     * Convert parameter to JSON array
     * @return array<string, mixed>
     */
    public function jsonSerialize()
    {
        return $this->data();
    }

    /**
     * Return the value for the given property
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        //convert to camel
        $key = Str::camel($name, true);
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return null;
    }

    /**
     * Return the value after cast
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    protected function getValue(string $attribute, $value)
    {
        $types = $this->getPropertyTypes();
        $property = $types[$attribute] ?? null;
        if ($property === null) {
            return $value;
        }

        $maps = $this->getPropertiesCastMaps();

        $type = $property[0];
        $allowNull = $property[1];

        if (
            in_array($type, ['array', 'object']) === false &&
            strlen((string) $value) === 0 && $allowNull
        ) {
            return null;
        }

        $closure = $maps[$type] ?? fn($value) => $value;

        return $closure($value);
    }

    /**
     * Return the properties of this class
     * @return array<string, string>
     */
    protected function getPropertyTypes(): array
    {
        $props = [];

        $reflectionClass = new ReflectionClass($this);
        /** @var ReflectionProperty[] $properties */
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            /** @var ReflectionNamedType|null $type */
            $type = $property->getType();
            if ($type !== null && $type->isBuiltin()) {
                $props[$property->getName()] = [$type->getName(), $type->allowsNull()];
            }
        }


        return $props;
    }

    /**
     * Return the properties cast maps
     * @return array<string, Closure>
     */
    protected function getPropertiesCastMaps(): array
    {
        return [
            'int' => fn($value) => intval($value),
            'float' => fn($value) => floatval($value),
            'double' => fn($value) => doubleval($value),
            'bool' => fn($value) => boolval($value),
            'string' => fn($value) => strval($value),
            'array' => fn($value) => (array) $value,
        ];
    }
}

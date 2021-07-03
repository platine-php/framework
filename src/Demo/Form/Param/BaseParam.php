<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine\Framework\Demo\Form\Param;

use Platine\Orm\Entity;
use Platine\Stdlib\Helper\Str;

class BaseParam
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

            $setterMethod = 'set' . ucfirst($key);
            if (method_exists($this, $setterMethod)) {
                $this->{$setterMethod}($value);
            } elseif (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

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
     * Return the value for the given property
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return null;
    }
}

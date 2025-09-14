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

use Platine\Framework\Audit\Auditor;
use Platine\Framework\Audit\Enum\EventType;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\DataMapper;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Php;
use Platine\Stdlib\Helper\Str;

/**
 * @class EntityHelper
 * @package Platine\Framework\Helper
 * @template TEntity as Entity
 */
class EntityHelper
{
    public const NONE    = 0;
    public const DELETE    = 1;
    public const CREATE  = 2;
    public const UPDATE    = 4;
    public const ALL = 7;


    /**
     * Whether to ignore audit
     * @var bool
     */
    protected bool $ignore = false;

    /**
     * Create new instance
     * @param Auditor $auditor
     */
    public function __construct(
        protected Auditor $auditor,
        protected AuthenticationInterface $authentication,
    ) {
    }

    /**
     *
     * @return bool
     */
    public function isIgnore(): bool
    {
        return $this->ignore;
    }

    /**
     *
     * @param bool $ignore
     * @return $this
     */
    public function setIgnore(bool $ignore): self
    {
        $this->ignore = $ignore;
        return $this;
    }


    /**
     * Subscribe to entity event "save", "update", "delete"
     * @param EntityMapperInterface<TEntity> $mapper
     * @param int $type
     * @param array<string> $ignoreFields
     * @return void
     */
    public function subscribeEvents(
        EntityMapperInterface $mapper,
        int $type = self::ALL,
        array $ignoreFields = []
    ): void {
        if ($this->authentication->isLogged() === false) {
            return;
        }

        $auditor = $this->auditor;
        $ignore = $this->ignore;
        $fieldIgnores = [
            ...$ignoreFields,
            ...['password', 'created_at', 'updated_at'],
        ];

        if ($type & self::CREATE) {
            $mapper->on('save', function (
                Entity $entity,
                DataMapper $dm
            ) use (
                $auditor,
                $ignore,
                $fieldIgnores
) {
                if ($ignore) {
                    return;
                }

                $data = $entity->jsonSerialize();
                $entityData = Arr::except($data, $fieldIgnores);
                $className = Php::getShortClassName($entity);

                $auditor->setDetail(sprintf(
                    'Create of "%s" %s',
                    $className,
                    Str::stringify($entityData)
                ))
                ->setEvent(EventType::CREATE)
                ->save();
            });
        }

        if ($type & self::UPDATE) {
            $mapper->on('update', function (
                Entity $entity,
                DataMapper $dm
            ) use (
                $auditor,
                $ignore,
                $fieldIgnores
            ) {
                if ($ignore) {
                    return;
                }

                $data = $entity->jsonSerialize();
                $entityData = Arr::except($data, $fieldIgnores);
                $className = Php::getShortClassName($entity);

                $auditor->setDetail(sprintf(
                    'Update of "%s" %s',
                    $className,
                    Str::stringify($entityData)
                ))
                ->setEvent(EventType::UPDATE)
                ->save();
            });
        }

        if ($type & self::DELETE) {
            $mapper->on('delete', function (
                Entity $entity,
                DataMapper $dm
            ) use (
                $auditor,
                $ignore,
                $fieldIgnores
            ) {
                if ($ignore) {
                    return;
                }

                $data = $entity->jsonSerialize();
                $entityData = Arr::except($data, $fieldIgnores);
                $className = Php::getShortClassName($entity);

                $auditor->setDetail(sprintf(
                    'Delete of "%s" %s',
                    $className,
                    Str::stringify($entityData)
                ))
                ->setEvent(EventType::DELETE)
                ->save();
            });
        }
    }

    /**
     * Return the changes between two entities
     * @param Entity|null $original
     * @param Entity|null $updated
     * @param array<string, array<string, mixed>> $fields
     * @return array<array{name: string, old:mixed, new:mixed}>
     */
    public static function getEntityChanges(
        ?Entity $original,
        ?Entity $updated,
        array $fields = []
    ): array {
        if ($original === null && $updated === null) {
            return [];
        }

        $oldColumnValue = null;
        $oldValue = null;
        $newColumnValue = null;
        $newValue = null;

        $results = [];

        // Closure to set entity relation data
        $setRelation = function (Entity $e, array|string $relation): string {
            $relation = Arr::wrap($relation);
            $text = [];
            foreach ($relation as $val) {
                $text[] =  $e->{$val};
            }
            return Arr::toString($text, ' ');
        };

        foreach ($fields as $field => $row) {
            $displayField = $row['display'] ?? $field;
            $displayText = $row['description'] ?? $field;
            $relation = $row['relation'] ?? $field;
            $enum = $row['enum'] ?? null;
            if ($original !== null) {
                $oldColumnValue = $original->{$field};
                $oldValue = $original->{$displayField};
            }

            if ($updated !== null) {
                $newColumnValue = $updated->{$field};
                $newValue = $updated->{$displayField};
            }

            if ($oldColumnValue !== $newColumnValue) {
                if ($oldValue instanceof Entity) {
                    $oldValue = $setRelation($oldValue, $relation);
                }

                if ($newValue instanceof Entity) {
                    $newValue = $setRelation($newValue, $relation);
                }

                if ($enum !== null) {
                    $oldValue = $enum[$oldValue] ?? '';
                    $newValue = $enum[$newValue] ?? '';
                }

                $results[] = [
                    'name' => $displayText,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $results;
    }

    /**
     * Return the changes between the given attributes
     * @param array<int, array<string, mixed>> $original
     * @param array<int, array<string, mixed>> $updated
     * @return array<array{name: string, old:mixed, new:mixed}>
     */
    public static function getAttributeChanges(
        array $original,
        array $updated
    ): array {
        $originalKeys = array_keys($original);
        $updatedKeys = array_keys($updated);
        $insertKeys = array_diff($updatedKeys, $originalKeys);
        $results = [];

        foreach ($original as $attrId => $data) {
            $updateValue = $updated[$attrId]['value'] ?? null;
            if ($data['value'] !== $updateValue) {
                $results[] = [
                    'name' => $data['name'],
                    'old' => $data['value'],
                    'new' => $updateValue,
                ];
            }
        }

        foreach ($insertKeys as $attrId) {
            $value = $updated[$attrId]['value'] ?? null;
            $name = $updated[$attrId]['name'] ?? null;

            $results[] = [
                'name' => $name,
                'old' => null,
                'new' => $value,
            ];
        }

        return $results;
    }
}

<?php

/**
 * Platine PHP
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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
 *  @file BaseMakeActionCommand.php
 *
 *  The action base make command class
 *
 *  @package    Platine\Framework\Console
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console;

use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\MakeCommand;
use Platine\Stdlib\Helper\Json;
use Platine\Stdlib\Helper\Str;

/**
 * @class BaseMakeActionCommand
 * @package Platine\Framework\Console
 */
abstract class BaseMakeActionCommand extends MakeCommand
{
    /**
     * The form parameter class name
     * @var class-string
     */
    protected string $paramClass;

    /**
     * The form validation class name
     * @var class-string
     */
    protected string $validatorClass;

    /**
     * The entity class name
     * @var class-string
     */
    protected string $entityClass;

    /**
     * The repository class name
     * @var class-string
     */
    protected string $repositoryClass;

    /**
     * Create new instance
     * @param Application $application
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $application,
        Filesystem $filesystem
    ) {
        parent::__construct($application, $filesystem);

        $this->addOption(
            '-c|--fields',
            'The entity fields. Example field1:param1,field2:param2,field3',
            null,
            false
        );

        $this->addOption(
            '-i|--fields-unique',
            'The entity unique fields. Example field1:param1,field2:param2,field3',
            null,
            false
        );

        $this->addOption(
            '-o|--fields-order',
            'The entity orders fields. Example field1:ASC,field2:DESC,field3',
            null,
            false
        );

        $this->addOption(
            '-t|--template-prefix',
            'The template prefix',
            null,
            false
        );

        $this->addOption(
            '-r|--route-prefix',
            'The route name prefix',
            null,
            false
        );

        $this->addOption(
            '-e|--message-not-found',
            'The entity not found error message',
            'This record doesn\'t exist',
            false
        );

        $this->addOption(
            '-l|--message-duplicate',
            'The entity duplicate error message',
            'This record already exist',
            false
        );

        $this->addOption(
            '-a|--message-create',
            'The entity successfully create message',
            'Data successfully created',
            false
        );

        $this->addOption(
            '-u|--message-update',
            'The entity successfully update message',
            'Data successfully updated',
            false
        );

        $this->addOption(
            '-d|--message-delete',
            'The entity successfully delete message',
            'Data successfully deleted',
            false
        );

        $this->addOption(
            '-p|--message-process-error',
            'The entity processing error message',
            'Data processing error',
            false
        );

        $this->addOption(
            '-j|--config',
            'Use JSON config file for options',
            null,
            false
        );

        $this->addOption(
            '-b|--entity-context-key',
            'The entity context key name',
            'entity',
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);

        // Load configuration file if exist
        $this->loadConfig();

        $this->recordResourceClasses();
    }

    /**
     * Record class properties
     * @return void
     */
    protected function recordProperties(): void
    {
        $io = $this->io();

        $writer = $io->writer();

        $writer->boldYellow('Enter the properties list (empty value to finish):', true);
        $value = '';
        while ($value !== null) {
            $value = $io->prompt('Property full class name', null, null, false);

            if (!empty($value)) {
                $value = trim($value);
                if (!class_exists($value) && !interface_exists($value)) {
                    $writer->boldWhiteBgRed(sprintf('The class [%s] does not exists', $value), true);
                } else {
                    $shortClass = $this->getClassBaseName($value);
                    $name = Str::camel($shortClass, true);
                    //replace"interface", "abstract"
                    $nameClean = str_ireplace(['interface', 'abstract'], '', $name);

                    $this->properties[$value] = [
                        'name' => $nameClean,
                        'short' => $shortClass,
                    ];
                }
            }
        }
    }

    /**
     * Record the resource classes
     * @return void
     */
    protected function recordResourceClasses(): void
    {
        $io = $this->io();

        $paramClass = $io->prompt('Enter the form parameter full class name', null);
        while (!class_exists($paramClass)) {
            $paramClass = $io->prompt('Class does not exists, please enter the form parameter full class name', null);
        }

        $this->paramClass = $paramClass;

        $validatorClass = $io->prompt('Enter the form validator full class name', null);
        while (!class_exists($validatorClass)) {
            $validatorClass = $io->prompt(
                'Class does not exists, please enter the form validator full class name',
                null
            );
        }

        $this->validatorClass = $validatorClass;

        $entityClass = $io->prompt('Enter the entity full class name', null);
        while (!class_exists($entityClass)) {
            $entityClass = $io->prompt('Class does not exists, please enter the entity full class name', null);
        }

        $this->entityClass = $entityClass;

        $repositoryClass = $io->prompt('Enter the repository full class name', null);
        while (!class_exists($repositoryClass)) {
            $repositoryClass = $io->prompt('Class does not exists, please enter the repository full class name', null);
        }

        $this->repositoryClass = $repositoryClass;
    }

    /**
     * Add new property
     * @param class-string $value
     * @param string|null $name
     * @return $this
     */
    protected function addProperty(string $value, ?string $name = null): self
    {
        $shortClass = $this->getClassBaseName($value);
        if ($name === null) {
            $name = Str::camel($shortClass, true);
        }

        //replace"interface", "abstract"
        $nameClean = str_ireplace(['interface', 'abstract'], '', $name);

        $this->properties[$value] = [
            'name' => $nameClean,
            'short' => $shortClass,
        ];

        return $this;
    }

    /**
     * Return the property name
     * @param class-string $value
     * @return string
     */
    protected function getPropertyName(string $value): string
    {
        if (!isset($this->properties[$value])) {
            return '';
        }

        return $this->properties[$value]['name'];
    }


    /**
     * Return the route prefix
     * @return string
     */
    protected function getTemplatePrefix(): string
    {
        $templatePrefix = $this->getOptionValue('templatePrefix');
        if ($templatePrefix === null) {
            $actionName = $this->getShortClassName($this->className);
            $templatePrefix = Str::snake(str_ireplace('action', '', $actionName));
        }

        return $templatePrefix;
    }

    /**
     * Return the entity context key
     * @param bool $isKey
     * @return string
     */
    protected function getEntityContextKey(bool $isKey = true): string
    {
        $key = (string) $this->getOptionValue('entityContextKey');
        if (!empty($key)) {
            if ($isKey) {
                $key = Str::snake($key, '_');
            } else {
                $key = Str::camel($key, true);
            }
        }

        return $key;
    }

    /**
     * Return the route prefix
     * @return string
     */
    protected function getRoutePrefix(): string
    {
        $routePrefix = $this->getOptionValue('routePrefix');
        if ($routePrefix === null) {
            $actionName = $this->getShortClassName($this->className);
            $routePrefix = Str::snake(str_ireplace('action', '', $actionName));
        }

        return $routePrefix;
    }

    /**
     * Return the route name
     * @param string $value
     * @return string
     */
    protected function getRouteName(string $value): string
    {
        $routePrefix = $this->getRoutePrefix();
        return sprintf('%s_%s', $routePrefix, $value);
    }

    /**
     * Return the form parameter method name of the given name
     * @param string $field
     * @return string
     */
    protected function getFormParamMethodName(string $field): string
    {
        return sprintf('get%s', Str::camel($field, false));
    }

    /**
     * Return the message
     * @param string $option
     * @return string|null
     */
    protected function getMessage(string $option): ?string
    {
        $message = (string) $this->getOptionValue($option);
        if (!empty($message)) {
            $message = addslashes($message);
        }

        return $message;
    }

    /**
     * Return the template for form parameter entity field
     * @param string $field
     * @param string $param
     * @param bool $isLast
     * @return string
     */
    protected function getFormParamEntityFieldTemplate(
        string $field,
        string $param,
        bool $isLast = false
    ): string {
        $fieldMethodName = $this->getFormParamMethodName($param);
        return sprintf('\'%s\' => $formParam->%s(),', $field, $fieldMethodName) . ($isLast ? PHP_EOL : '');
    }

    /**
     * Format option fields
     * @param string $values
     * @return array<string|int, string>
     */
    protected function formatFields(string $values): array
    {
        $result = [];
        $fields = (array) explode(',', $values);
        foreach ($fields as $field) {
            $column = $field;
            $param = $field;
            $value = (array) explode(':', $field);
            if (isset($value[0])) {
                $column = $value[0];
            }

            if (isset($value[1])) {
                $param = $value[1];
            }

            $result[$column] = $param;
        }

        return $result;
    }

    /**
     * Format fields
     * @param array<string, string> $values
     * @param bool $orderField
     * @return string
     */
    protected function formatFieldStr(array $fields, bool $orderField = false): string
    {
        $result = '';
        foreach ($fields as $field => $param) {
            if ($orderField) {
                $order = 'ASC';
                if ($param === 'DESC') {
                    $order = 'DESC';
                }

                if ($order === 'ASC') {
                    $result .= sprintf('\'%s\', ', $field);
                } else {
                    $result .= sprintf('\'%s\' => \'DESC\', ', $field);
                }
            } else {
                if ($field === $param) {
                    $result .= sprintf('\'%s\', ', $field);
                } else {
                    $result .= sprintf('\'%s\' => \'%s\', ', $field, $param);
                }
            }
        }

        return rtrim($result, ', ');
    }

    /**
     * Load JSON configuration file if exist
     * @return void
     */
    protected function loadConfig(): void
    {
        $filename = $this->getOptionValue('config');
        if (!empty($filename)) {
            $file = $this->filesystem->file($filename);
            if ($file->exists() && $file->isReadable()) {
                $content = $file->read();
                /** @var array<string, string> $config */
                $config = Json::decode($content, true);
                foreach ($config as $option => $value) {
                    $optionKey = Str::camel($option, true);
                    $this->values[$optionKey] = $value;
                }
            }
        }
    }
}

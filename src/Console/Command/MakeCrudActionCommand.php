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
 *  @file MakeCrudActionCommand.php
 *
 *  The Command to generate new CRUD action class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Stdlib\Helper\Json;
use Platine\Stdlib\Helper\Str;

/**
 * @class MakeCrudActionCommand
 * @package Platine\Framework\Console\Command
 */
class MakeCrudActionCommand extends MakeResourceActionCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'crud';

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
        $this->setName('make:crud')
              ->setDescription('Command to generate platine CRUD action');
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
     * {@inheritdoc}
     */
    public function getClassTemplate(): string
    {
        return <<<EOF
        <?php
        
        declare(strict_types=1);
        
        namespace %namespace%;
        
        use Platine\Framework\Helper\Flash;
        use Platine\Framework\Http\Action\CrudAction;
        use Platine\Framework\Http\RouteHelper;
        use Platine\Lang\Lang;
        use Platine\Logger\LoggerInterface;
        use Platine\Pagination\Pagination;
        use Platine\Template\Template;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        * @extends CrudAction<%base_entity_class%>
        */
        class %classname% extends CrudAction
        {
            
            %attributes%
        
            /**
            * Create new instance
            * {@inheritdoc}
            * @param %base_repository% \$repository
            */
            public function __construct(
                Lang \$lang,
                Pagination \$pagination,
                Template \$template,
                Flash \$flash,
                RouteHelper \$routeHelper,
                LoggerInterface \$logger,
                %base_repository% \$repository
            ) {
                parent::__construct(
                    \$lang,
                    \$pagination,
                    \$template,
                    \$flash,
                    \$routeHelper,
                    \$logger
                );
                \$this->repository = \$repository;
            }
        }
        
        EOF;
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
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        $replace = '';
        $fields = $this->getFieldsPropertyBody($content);
        if (!empty($fields)) {
            $replace .= $fields;
        }

        return str_replace('%attributes%', $replace, $content);
    }

    /**
     * Return the fields property body
     * @param string $content
     * @return string
     */
    protected function getFieldsPropertyBody(string $content): string
    {
        $fields = $this->getOptionValue('fields');
        if ($fields === null) {
            return '';
        }
        $result = <<<EOF
        /**
            * {@inheritdoc}
            */
            protected array \$fields = ['name', 'description'];
        EOF;

        return $result;
    }



    /**
     * Return the template for order by
     * @return string
     */
    protected function getOrderByFieldsPropertyBody(): string
    {
        $result = '';
        $orderFields = $this->getOptionValue('fieldsOrder');

        if ($orderFields !== null) {
            $fields = (array) explode(',', $orderFields);
            $i = 1;
            foreach ($fields as $field) {
                $column = $field;
                $dir = 'ASC';
                $orderField = (array) explode(':', $field);
                if (isset($orderField[0])) {
                    $column = $orderField[0];
                }

                if (isset($orderField[1]) && in_array(strtolower($orderField[1]), ['asc', 'desc'])) {
                    $dir = $orderField[1];
                }

                $result .= ($i > 1 ? "\t\t\t\t\t    " : '') .
                        sprintf('->orderBy(\'%s\', \'%s\')', $column, Str::upper($dir)) .
                        (count($fields) > $i ? PHP_EOL : '');
                $i++;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        $uses = parent::getUsesContent();

        $uses .= $this->getUsesTemplate($this->entityClass);
        $uses .= $this->getUsesTemplate($this->paramClass);
        $uses .= $this->getUsesTemplate($this->validatorClass);
        $uses .= $this->getUsesTemplate($this->repositoryClass);

        return <<<EOF
        $uses
        EOF;
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

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

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Config\Model\Configuration;
use Platine\Framework\Console\MakeCommand;
use Platine\Orm\Entity;
use Platine\Stdlib\Helper\Str;

/**
 * @class MakeDatabaseConfigCommand
 * @package Platine\Framework\Console\Command
 */
class MakeDatabaseConfigCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'database config';

    /**
     * Create new instance
     * @param Application $application
     * @param Filesystem $filesystem
     * @param AppDatabaseConfig $dbConfig
     */
    public function __construct(
        Application $application,
        Filesystem $filesystem,
        protected AppDatabaseConfig $dbConfig,
    ) {
        parent::__construct($application, $filesystem);

        $this->setName('make:dbconfig')
               ->setDescription('Command to generate class that hold database configuration value');

        $this->addOption('-c|--config-entity', 'The configuration entity class', Configuration::class, true);
        $this->addOption('-m|--module', 'The configuration module to use', null, false);
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
        
        use Platine\Framework\Config\AppDatabaseConfig;
        %uses%

        /**
        * +=+=+ NOTE: THIS IS THE GENERATED CLASS DO NOT MODIFY IT +=+=+
        *
        * @class %classname%
        * @package %namespace%
        * @template TDbConfigurationEntity as \%config_entity%
        */
        class %classname%
        {
            /**
            * Create new instance
            * @param AppDatabaseConfig<TDbConfigurationEntity> \$dbConfig
            */
            public function __construct(protected AppDatabaseConfig \$dbConfig)
            {
            }
        
            %config_content%
        }
        
        EOF;
    }

    /**
     * Generate the method of the given entity
     * @param Entity $entity
     * @param string|null $module
     * @return string
     */
    protected function getConfigMethod(Entity $entity, ?string $module = null): string
    {
        $types = $this->getDataTypeMaps();
        $methodTemplate = $this->getMethodTemplate();

        $method = sprintf('%s_%s', $entity->module, $entity->name);
        if ($module !== null) {
            $method = $entity->name;
        }
        $methodName = Str::camel($method, false);

        $strMethodName = str_replace('%method_name%', $methodName, $methodTemplate);
        $strType = str_replace('%type%', $types[$entity->type][0], $strMethodName);
        $strDefault = str_replace('%default_value%', $types[$entity->type][1], $strType);
        $strModule = str_replace('%module%', $entity->module, $strDefault);
        $strKey = str_replace('%key%', $entity->name, $strModule);

        return $strKey;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        $methods = '';
        $module = $this->getOptionValue('module');
        $results = $this->dbConfig->getLoader()->all();
        foreach ($results as $row) {
            if ($module === null || $row->module === $module) {
                $methods .= $this->getConfigMethod($row, $module);
            }
        }

        $configEntity = $this->getOptionValue('configEntity');
        $configEntityContent = str_replace('%config_entity%', $configEntity, $content);

        return str_replace('%config_content%', $methods, $configEntityContent);
    }

    /**
     * Return the data type mapping
     * @return array<string, array{0:string, 1:string}>
     */
    protected function getDataTypeMaps(): array
    {
        return [
            'integer' => ['int', '0'],
            'double' => ['float', '0.0'],
            'float' => ['float', '0.0'],
            'array' => ['array', '[]'],
            'object' => ['object', 'null'],
            'boolean' => ['bool', 'false'],
            'string' => ['string', '\'\''],
        ];
    }

    /**
     * Return the configuration method template
     * @return string
     */
    public function getMethodTemplate(): string
    {
        return <<<EOF
        /**
            * Return the configuration value for module "%module%" and key "%key%"
            * @param %type%|null \$default the default value
            *
            * @return %type% the configuration value if exist or default
            */
            public function get%method_name%(?%type% \$default = %default_value%): %type%
            {
                return \$this->dbConfig->get('%module%.%key%', \$default);
            }
            
            
        EOF;
    }
}

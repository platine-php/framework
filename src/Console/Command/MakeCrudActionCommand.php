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

use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\BaseMakeActionCommand;
use Platine\Stdlib\Helper\Str;

/**
 * @class MakeCrudActionCommand
 * @package Platine\Framework\Console\Command
 */
class MakeCrudActionCommand extends BaseMakeActionCommand
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
        * @extends CrudAction<%base_entity%>
        */
        class %classname% extends CrudAction
        {
            
            %attributes%
            /**
            * {@inheritdoc}
            */
            protected string \$paramClass = %base_param%::class;

            /**
            * {@inheritdoc}
            */
            protected string \$validatorClass = %base_validator%::class;
        
            /**
            * Create new instance
            * {@inheritdoc}
            * @param %base_repository%<%base_entity%> \$repository
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
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();
        $entityBaseClass = $this->getClassBaseName($this->entityClass);
        $repositoryBaseClass = $this->getClassBaseName($this->repositoryClass);
        $paramBaseClass = $this->getClassBaseName($this->paramClass);
        $validatorBaseClass = $this->getClassBaseName($this->validatorClass);

        $replace = '';
        $fields = $this->getFieldsPropertyBody();
        if (!empty($fields)) {
            $replace .= $fields . PHP_EOL . PHP_EOL;
        }

        $orderFields = $this->getOrderByFieldsPropertyBody();
        if (!empty($orderFields)) {
            $replace .= $orderFields . PHP_EOL . PHP_EOL;
        }

        $uniqueFields = $this->getUniqueFieldsPropertyBody();
        if (!empty($uniqueFields)) {
            $replace .= $uniqueFields . PHP_EOL . PHP_EOL;
        }

        $templatePrefix = $this->getTemplatePrefix();
        if (!empty($templatePrefix)) {
            $replace .= <<<EOF
            /**
            * {@inheritdoc}
            */
            protected string \$templatePrefix = '$templatePrefix';
        
        
        EOF;
        }

        $routePrefix = $this->getRoutePrefix();
        if (!empty($routePrefix)) {
            $replace .= <<<EOF
            /**
            * {@inheritdoc}
            */
            protected string \$routePrefix = '$routePrefix';
        
        
        EOF;
        }

        $entityContextName = $this->getEntityContextKey(true);
        $optionEntityContext = $this->getOptionForArgument('--entity-context-key');
        if ($optionEntityContext !== null && $optionEntityContext->getDefault() !== $entityContextName) {
            $replace .= <<<EOF
            /**
            * {@inheritdoc}
            */
            protected string \$entityContextName = '$entityContextName';
        
        
        EOF;
        }

        $messages = $this->getMessageTemplates();
        if (!empty($messages)) {
            $replace .= $messages;
        }


        $contentBaseEntity = str_replace('%base_entity%', $entityBaseClass, $content);
        $contentBaseRepository = str_replace('%base_repository%', $repositoryBaseClass, $contentBaseEntity);
        $contentBaseParam = str_replace('%base_param%', $paramBaseClass, $contentBaseRepository);
        $contentBaseValidator = str_replace('%base_validator%', $validatorBaseClass, $contentBaseParam);

        return str_replace('%attributes%', $replace, $contentBaseValidator);
    }

    /**
     * Return the messages template
     * @return string
     */
    protected function getMessageTemplates(): string
    {
        $replace = '';

        $messages = [
            'create',
            'update',
            'delete',
            'duplicate',
            'not-found',
            'process-error',
        ];

        foreach ($messages as $val) {
            $optionName = sprintf('--message-%s', $val);
            $optionKey = sprintf('message%s', Str::camel($val, false));

            $message = $this->getMessage($optionKey);
            $option = $this->getOptionForArgument($optionName);
            if ($option !== null && addslashes($option->getDefault()) !== $message) {
                $replace .= <<<EOF
                /**
                * {@inheritdoc}
                */
                protected string \${$optionKey} = '$message';


            EOF;
            }
        }

        return $replace;
    }

    /**
     * Return the fields property body
     * @return string
     */
    protected function getFieldsPropertyBody(): string
    {
        $fields = $this->getOptionValue('fields');
        if ($fields === null) {
            return '';
        }

        $columns = $this->formatFields($fields);
        $str = $this->formatFieldStr($columns);

        $result = <<<EOF
        /**
            * {@inheritdoc}
            */
            protected array \$fields = [$str];
        EOF;

        return $result;
    }

    /**
     * Return the order by fields property body
     * @return string
     */
    protected function getOrderByFieldsPropertyBody(): string
    {
        $fields = $this->getOptionValue('fieldsOrder');

        if ($fields === null) {
            return '';
        }

        $columns = $this->formatFields($fields);
        $str = $this->formatFieldStr($columns, true);

        $result = <<<EOF
            /**
            * {@inheritdoc}
            */
            protected array \$orderFields = [$str];
        EOF;

        return $result;
    }

    /**
     * Return the unique fields property body
     * @return string
     */
    protected function getUniqueFieldsPropertyBody(): string
    {
        $fields = $this->getOptionValue('fieldsUnique');

        if ($fields === null) {
            return '';
        }

        $columns = $this->formatFields($fields);
        $str = $this->formatFieldStr($columns);

        $result = <<<EOF
            /**
            * {@inheritdoc}
            */
            protected array \$uniqueFields = [$str];
        EOF;

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
}

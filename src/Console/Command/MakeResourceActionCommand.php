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
 *  @file MakeResourceActionCommand.php
 *
 *  The Command to generate new resource action class
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
use Platine\Framework\Console\MakeCommand;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Http\RouteHelper;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Pagination\Pagination;
use Platine\Stdlib\Helper\Json;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Template;

/**
 * @class MakeResourceActionCommand
 * @package Platine\Framework\Console\Command
 */
class MakeResourceActionCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'resource';

    /**
     * The form parameter class name
     * @var class-string
     */
    protected string $paramClass;

    /**
     * The form validator class name
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
        $this->setName('make:resource')
              ->setDescription('Command to generate platine resource action');

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

        $baseClasses = $this->getBaseClasses();

        foreach ($baseClasses as $value) {
            $this->addProperty($value);
        }

        $this->recordResourceClasses();

        $this->recordProperties();

        $this->addProperty($this->repositoryClass);
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
        
        use Exception;
        use Platine\Http\ResponseInterface;
        use Platine\Http\ServerRequestInterface;
        use Platine\Framework\Http\RequestData;
        use Platine\Framework\Http\Response\TemplateResponse;
        use Platine\Framework\Http\Response\RedirectResponse;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname%
        {
            
            %properties%
        
            %constructor%
        
            /**
            * List all entities
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function index(ServerRequestInterface \$request): ResponseInterface
            {
                %method_body_index%
            }
        
            /**
            * List entity detail
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function detail(ServerRequestInterface \$request): ResponseInterface
            {
                %method_body_detail%
            }
        
            /**
            * Create new entity
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function create(ServerRequestInterface \$request): ResponseInterface
            {
                %method_body_create%
            }
        
            /**
            * Update existing entity
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function update(ServerRequestInterface \$request): ResponseInterface
            {
                %method_body_update%
            }
        
            /**
            * Delete the entity
            * @param ServerRequestInterface \$request
            * @return ResponseInterface
            */
            public function delete(ServerRequestInterface \$request): ResponseInterface
            {
                %method_body_delete%
            }
        }
        
        EOF;
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
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();


        $contentIndex = $this->getIndexMethodBody($content);
        $contentDetail = $this->getDetailMethodBody($contentIndex);
        $contentCreate = $this->getCreateMethodBody($contentDetail);
        $contentUpdate = $this->getUpdateMethodBody($contentCreate);
        $contentDelete = $this->getDeleteMethodBody($contentUpdate);

        return $contentDelete;
    }

    /**
     * Return the index method body
     * @param string $content
     * @return string
     */
    protected function getIndexMethodBody(string $content): string
    {
        $repositoryName = $this->getPropertyName($this->repositoryClass);
        $templatePrefix = $this->getTemplatePrefix();

        $orderByTemplate = $this->getOrderByTemplate();

        $result = <<<EOF
        \$context = [];
                \$param = new RequestData(\$request);
                \$totalItems = \$this->{$repositoryName}->query()
                                                       ->count('id');

                \$currentPage = (int) \$param->get('page', 1);

                \$this->pagination->setTotalItems(\$totalItems)
                                ->setCurrentPage(\$currentPage);

                \$limit = \$this->pagination->getItemsPerPage();
                \$offset = \$this->pagination->getOffset();

                \$results = \$this->{$repositoryName}->query()
                                                    ->offset(\$offset)
                                                    ->limit(\$limit)
                                                    $orderByTemplate
                                                    ->all();
                
                \$context['list'] = \$results;
                \$context['pagination'] = \$this->pagination->render();


                return new TemplateResponse(
                    \$this->template,
                    '$templatePrefix/list',
                    \$context
                );
        EOF;

        return str_replace('%method_body_index%', $result, $content);
    }

    /**
     * Return the detail method body
     * @param string $content
     * @return string
     */
    protected function getDetailMethodBody(string $content): string
    {
        $repositoryName = $this->getPropertyName($this->repositoryClass);
        $entityBaseClass = $this->getClassBaseName($this->entityClass);
        $templatePrefix = $this->getTemplatePrefix();
        $notFoundMessage = $this->getMessage('messageNotFound');

        $listRoute = $this->getRouteName('list');

        $entityContextKey = $this->getEntityContextKey(true);
        $entityContextName = $this->getEntityContextKey(false);

        $result = <<<EOF
        \$context = [];
                \$id = (int) \$request->getAttribute('id');

                /** @var $entityBaseClass|null \$$entityContextName */
                \$$entityContextName = \$this->{$repositoryName}->find(\$id);

                if (\$$entityContextName === null) {
                    \$this->flash->setError(\$this->lang->tr('$notFoundMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$listRoute')
                    );
                }
                \$context['$entityContextKey'] = \$$entityContextName;
                        
                return new TemplateResponse(
                    \$this->template,
                    '$templatePrefix/detail',
                    \$context
                );
        EOF;


        return str_replace('%method_body_detail%', $result, $content);
    }

    /**
     * Return the create method body
     * @param string $content
     * @return string
     */
    protected function getCreateMethodBody(string $content): string
    {
        $repositoryName = $this->getPropertyName($this->repositoryClass);
        $formParamBaseClass = $this->getClassBaseName($this->paramClass);
        $validatorBaseClass = $this->getClassBaseName($this->validatorClass);
        $entityBaseClass = $this->getClassBaseName($this->entityClass);
        $templatePrefix = $this->getTemplatePrefix();
        $listRoute = $this->getRouteName('list');
        $createMessage = $this->getMessage('messageCreate');
        $processErrorMessage = $this->getMessage('messageProcessError');

        $uniqueCheckStr = $this->getUniqueFieldCheckTemplate(true);
        $fieldTemplates = $this->getEntityFieldsTemplate(true);

        $entityContextName = $this->getEntityContextKey(false);

        $result = <<<EOF
        \$context = [];
                \$param = new RequestData(\$request);
                
                \$formParam = new $formParamBaseClass(\$param->posts());
                \$context['param'] = \$formParam;
                
                if (\$request->getMethod() === 'GET') {
                    return new TemplateResponse(
                        \$this->template,
                        '$templatePrefix/create',
                        \$context
                    );
                }
                
                \$validator = new $validatorBaseClass(\$formParam, \$this->lang);
                if (\$validator->validate() === false) {
                    \$context['errors'] = \$validator->getErrors();

                    return new TemplateResponse(
                        \$this->template,
                        '$templatePrefix/create',
                        \$context
                    );
                }
                
                $uniqueCheckStr

                /** @var $entityBaseClass \$$entityContextName */
                \$$entityContextName = \$this->{$repositoryName}->create([
                   $fieldTemplates
                ]);
                
                try {
                    \$this->{$repositoryName}->save(\$$entityContextName);

                    \$this->flash->setSuccess(\$this->lang->tr('$createMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$listRoute')
                    );
                } catch (Exception \$ex) {
                    \$this->logger->error('Error when saved the data {error}', ['error' => \$ex->getMessage()]);

                    \$this->flash->setError(\$this->lang->tr('$processErrorMessage'));

                    return new TemplateResponse(
                        \$this->template,
                        '$templatePrefix/create',
                        \$context
                    );
                }
        EOF;


        return str_replace('%method_body_create%', $result, $content);
    }

    /**
     * Return the update method body
     * @param string $content
     * @return string
     */
    protected function getUpdateMethodBody(string $content): string
    {
        $repositoryName = $this->getPropertyName($this->repositoryClass);
        $formParamBaseClass = $this->getClassBaseName($this->paramClass);
        $validatorBaseClass = $this->getClassBaseName($this->validatorClass);
        $entityBaseClass = $this->getClassBaseName($this->entityClass);
        $templatePrefix = $this->getTemplatePrefix();
        $listRoute = $this->getRouteName('list');
        $detailRoute = $this->getRouteName('detail');
        $notFoundMessage = $this->getMessage('messageNotFound');
        $updateMessage = $this->getMessage('messageUpdate');
        $processErrorMessage = $this->getMessage('messageProcessError');

        $uniqueCheckStr = $this->getUniqueFieldCheckTemplate(false);
        $fieldTemplates = $this->getEntityFieldsTemplate(false);

        $entityContextKey = $this->getEntityContextKey(true);
        $entityContextName = $this->getEntityContextKey(false);

        $result = <<<EOF
        \$context = [];
                \$param = new RequestData(\$request);
                
                \$id = (int) \$request->getAttribute('id');

                /** @var $entityBaseClass|null \$$entityContextName */
                \$$entityContextName = \$this->{$repositoryName}->find(\$id);

                if (\$$entityContextName === null) {
                    \$this->flash->setError(\$this->lang->tr('$notFoundMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$listRoute')
                    );
                }
                \$context['$entityContextKey'] = \$$entityContextName;
                \$context['param'] = (new $formParamBaseClass())->fromEntity(\$$entityContextName);
                if (\$request->getMethod() === 'GET') {
                    return new TemplateResponse(
                        \$this->template,
                        '$templatePrefix/update',
                        \$context
                    );
                }
                \$formParam = new $formParamBaseClass(\$param->posts());
                \$context['param'] = \$formParam;
                
                \$validator = new $validatorBaseClass(\$formParam, \$this->lang);
                if (\$validator->validate() === false) {
                    \$context['errors'] = \$validator->getErrors();

                    return new TemplateResponse(
                        \$this->template,
                        '$templatePrefix/update',
                        \$context
                    );
                }
                
                $uniqueCheckStr

                $fieldTemplates
                
                try {
                    \$this->{$repositoryName}->save(\$$entityContextName);

                    \$this->flash->setSuccess(\$this->lang->tr('$updateMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$detailRoute', ['id' => \$id])
                    );
                } catch (Exception \$ex) {
                    \$this->logger->error('Error when saved the data {error}', ['error' => \$ex->getMessage()]);

                    \$this->flash->setError(\$this->lang->tr('$processErrorMessage'));

                    return new TemplateResponse(
                        \$this->template,
                        '$templatePrefix/update',
                        \$context
                    );
                }
        EOF;


        return str_replace('%method_body_update%', $result, $content);
    }

    /**
     * Return the delete method body
     * @param string $content
     * @return string
     */
    protected function getDeleteMethodBody(string $content): string
    {
        $repositoryName = $this->getPropertyName($this->repositoryClass);
        $entityBaseClass = $this->getClassBaseName($this->entityClass);
        $notFoundMessage = $this->getMessage('messageNotFound');
        $deleteMessage = $this->getMessage('messageDelete');
        $processErrorMessage = $this->getMessage('messageProcessError');

        $listRoute = $this->getRouteName('list');

        $entityContextName = $this->getEntityContextKey(false);

        $result = <<<EOF
        \$id = (int) \$request->getAttribute('id');

                /** @var $entityBaseClass|null \$$entityContextName */
                \$$entityContextName = \$this->{$repositoryName}->find(\$id);

                if (\$$entityContextName === null) {
                    \$this->flash->setError(\$this->lang->tr('$notFoundMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$listRoute')
                    );
                }

                try {
                    \$this->{$repositoryName}->delete(\$$entityContextName);

                    \$this->flash->setSuccess(\$this->lang->tr('$deleteMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$listRoute')
                    );
                } catch (Exception \$ex) {
                    \$this->logger->error('Error when delete the data {error}', ['error' => \$ex->getMessage()]);

                    \$this->flash->setError(\$this->lang->tr('$processErrorMessage'));

                    return new RedirectResponse(
                        \$this->routeHelper->generateUrl('$listRoute')
                    );
                }
        EOF;


        return str_replace('%method_body_delete%', $result, $content);
    }

    /**
     * Return the template for unique field check
     * @param bool $create
     * @return string
     */
    protected function getUniqueFieldCheckTemplate(bool $create = true): string
    {
        $repositoryName = $this->getPropertyName($this->repositoryClass);
        $templatePrefix = $this->getTemplatePrefix();
        $uniqueFields = $this->getOptionValue('fieldsUnique');
        $uniqueCheckStr = '';
        if ($uniqueFields !== null) {
            $duplicateMessage = $this->getMessage('messageDuplicate');

            $fields = (array) explode(',', $uniqueFields);
            $i = 1;
            $result = '';
            foreach ($fields as $field) {
                $column = $field;
                $param = $field;
                $uniqueField = (array) explode(':', $field);
                if (isset($uniqueField[0])) {
                    $column = $uniqueField[0];
                }

                if (isset($uniqueField[1])) {
                    $param = $uniqueField[1];
                }

                $result .= ($i > 1 ? "\t\t\t\t\t       " : '') .
                        $this->getFormParamEntityFieldTemplate($column, $param, count($fields) > $i);
                $i++;
            }

            $updateStr = $create ? '' : ' && $entityExist->id !== $id';
            $templateName = $create ? 'create' : 'update';

            $uniqueCheckStr = <<<EOF
            \$entityExist = \$this->{$repositoryName}->findBy([
                                                           $result
                                                       ]);
                    
                    if(\$entityExist !== null$updateStr){
                        \$this->flash->setError(\$this->lang->tr('$duplicateMessage'));

                        return new TemplateResponse(
                            \$this->template,
                            '$templatePrefix/$templateName',
                            \$context
                        );
                    }
            EOF;
        }

        return $uniqueCheckStr;
    }

    /**
     * Return the template for order by
     * @return string
     */
    protected function getOrderByTemplate(): string
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
     * Return the template for entity field for saving
     * @param bool $create
     * @return string
     */
    protected function getEntityFieldsTemplate(bool $create = true): string
    {
        $fields = $this->getOptionValue('fields');
        $result = '';
        if ($fields !== null) {
            $fields = (array) explode(',', $fields);
            $i = 1;

            foreach ($fields as $field) {
                $column = $field;
                $param = $field;
                $entityField = (array) explode(':', $field);
                if (isset($entityField[0])) {
                    $column = $entityField[0];
                }

                if (isset($entityField[1])) {
                    $param = $entityField[1];
                }

                $result .= ($i > 1 ? "\t   " : '') .
                        $this->getEntityRecordFieldTemplate($column, $param, count($fields) > $i, $create);
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

        return <<<EOF
        $uses
        EOF;
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
        if($name === null){
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
     * Return the base classes
     * @return array<class-string>
     */
    protected function getBaseClasses(): array
    {
        return [
            Lang::class,
            Pagination::class,
            Template::class,
            Flash::class,
            RouteHelper::class,
            LoggerInterface::class,
        ];
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
     * Return the template for entity record fields
     * @param string $field
     * @param string $param
     * @param bool $isLast
     * @param bool $create
     * @return string
     */
    protected function getEntityRecordFieldTemplate(
        string $field,
        string $param,
        bool $isLast = false,
        $create = true
    ): string {
        $fieldMethodName = $this->getFormParamMethodName($param);
        if ($create) {
            return sprintf('\'%s\' => $formParam->%s(),', $field, $fieldMethodName) . ($isLast ? PHP_EOL : '');
        }
        $entityContextName = $this->getEntityContextKey(false);
        return sprintf(
            '$%s->%s = $formParam->%s();',
            $entityContextName,
            $field,
            $fieldMethodName
        ) . ($isLast ? PHP_EOL : '');
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

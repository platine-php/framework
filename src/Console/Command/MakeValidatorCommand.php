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
 *  @file MakeValidatorCommand.php
 *
 *  The Make validator Command class
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
use Platine\Stdlib\Helper\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * @class MakeValidatorCommand
 * @package Platine\Framework\Console\Command
 */
class MakeValidatorCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'validator';

    /**
     * The form parameter class name
     * @var class-string
     */
    protected string $paramClass;

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
        $this->setName('make:validator')
               ->setDescription('Command to generate new validator class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);


        $io = $this->io();

        $paramClass = $io->prompt('Enter the form parameter full class name', null);
        while (!class_exists($paramClass)) {
            $paramClass = $io->prompt('Class does not exists, please enter the form parameter full class name', null);
        }

        $this->paramClass = $paramClass;
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
        
        use Platine\Framework\Form\Validator\AbstractValidator;
        use Platine\Lang\Lang;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname% extends AbstractValidator
        {
            /**
            * The parameter instance
            * @var %param_class%
            */
            protected %param_class% \$param;
        
            /**
            * Create new instance
            * @param %param_class% \$param
            * @param Lang \$lang
            */
           public function __construct(%param_class% \$param, Lang \$lang)
           {
               parent::__construct(\$lang);
               \$this->param = \$param;
           }
        
            /**
            * {@inheritdoc}
            */
            public function setValidationData(): void
            {
                %validation_data_body%
            }
        
            /**
            * {@inheritdoc}
            */
            public function setValidationRules(): void
            {
                %validation_rules_body%
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

        $formParameter =  $this->replaceFormParameterName($content);
        $validationDataBody =  $this->getValidationDataBody($formParameter);

        return $this->getValidationRulesBody($validationDataBody);
    }

    /**
     * Return the list of parameters properties
     * @return array<string, string>
     */
    protected function getParameterProperties(): array
    {
        $list = [];
        $reflection = new ReflectionClass($this->paramClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        if (!empty($methods)) {
            foreach ($methods as /** @var ReflectionMethod $method */ $method) {
                $returnType = $method->getReturnType();
                if ($returnType !== null && $returnType->getName() === 'string') {
                    $name = $method->name;
                    if (substr($name, 0, 3) === 'get') {
                        $field = str_replace('get', '', $name);
                        $list[Str::snake($field)] = $name;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Return the validation rules body
     * @param string $content
     * @return string
     */
    protected function getValidationRulesBody(string $content): string
    {
        $result = '';
        foreach ($this->getParameterProperties() as $field => $getter) {
            $result .= <<<EOF
            \$this->addRules('$field', [
            
                    ]);
                  
                    
            EOF;
        }
        return str_replace('%validation_rules_body%', $result, $content);
    }

    /**
     * Return the validation data body
     * @param string $content
     * @return string
     */
    protected function getValidationDataBody(string $content): string
    {
        $result = '';
        foreach ($this->getParameterProperties() as $field => $getter) {
            $result .= <<<EOF
            \$this->addData('$field', \$this->param->$getter());
                    
            EOF;
        }

        return str_replace('%validation_data_body%', $result, $content);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        return $this->getUsesTemplate($this->paramClass);
    }

    /**
     * Replace the form parameter name the entity body
     * @param string $content
     * @return string
     */
    protected function replaceFormParameterName(string $content): string
    {
        $paramName = basename($this->paramClass);

        return str_replace('%param_class%', $paramName, $content);
    }
}

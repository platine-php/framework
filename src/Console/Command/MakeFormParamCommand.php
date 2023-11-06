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
 *  @file MakeFormParamCommand.php
 *
 *  The Make form parameter Command class
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

/**
 * @class MakeFormParamCommand
 * @package Platine\Framework\Console\Command
 */
class MakeFormParamCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'form parameter';

    /**
     * Whether to create instance from entity
     * @var bool
     */
    protected bool $createInstanceFormEntity = false;

    /**
     * The list of properties entity field maps
     * @var array<string, string>
     */
    protected array $fromEntityMaps = [];

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
        $this->setName('make:param')
               ->setDescription('Command to generate new form parameter class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);

        $properties = [];

        $io = $this->io();
        $writer->boldYellow('Enter the properties list (empty value to finish):', true);
        $value = '';
        while ($value !== null) {
            $value = $io->prompt('Property name', null, null, false);

            if (!empty($value)) {
                $value = trim($value);
                $type = 'string';
                $required = true;
                $default = null;

                // 0 = field name, 1 = data type, 2 = required (true/false), 3 = default value
                $values = (array) explode(':', $value);
                if (isset($values[0]) && !empty($values[0])) {
                    $value = $values[0];
                }

                if (isset($values[1]) && !empty($values[1])) {
                    $type = $values[1];
                }

                if (isset($values[2])) {
                    $required = in_array($values[2], ['true', '1', 'yes', 'on', 'y']) ;
                }

                if (isset($values[3])) {
                    $default = $values[3];
                }

                $name = Str::camel($value, true);

                $properties[$name] = [
                    'name' => $name,
                    'short' => $type, // needed by super class
                    'type' => $type,
                    'required' => $required,
                    'default' => $default,
                ];
            }
        }

        $this->properties = $properties;

        if (!empty($this->properties)) {
            $this->createInstanceFormEntity = $io->confirm('Create instance from entity ?', 'y');

            if ($this->createInstanceFormEntity) {
                $list = [];
                foreach ($this->properties as $name => $info) {
                    $value = $io->prompt(
                        sprintf('Entity field name for [%s] (just enter to ignore)', $name),
                        null,
                        null,
                        false
                    );
                    if (!empty($value)) {
                        $list[$name] = $value;
                    }
                }

                if (!empty($list)) {
                    $this->fromEntityMaps = $list;
                }
            }
        }
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
        
        use Platine\Framework\Form\Param\BaseParam;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        * %template_entity%
        */
        class %classname% extends BaseParam
        {
            %properties%
            %from_entity%
            %getters%
            %setters%
        }
        
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        $fromEntity = $this->getFromEntityBody($content);
        $setters = $this->getSettersBody($fromEntity);

        return $this->getGettersBody($setters);
    }

    /**
     * Return the setters body
     * @param string $content
     * @return string
     */
    protected function getSettersBody(string $content): string
    {
        $result = '';
        foreach ($this->properties as $info) {
            $result .= $this->getSetterTemplate($info);
        }

        return str_replace('%setters%', $result, $content);
    }

    /**
     * Return the getter body
     * @param string $content
     * @return string
     */
    protected function getGettersBody(string $content): string
    {
        $result = '';

        foreach ($this->properties as $info) {
            $result .= $this->getGetterTemplate($info);
        }

        return str_replace('%getters%', $result, $content);
    }

    /**
     * Return the setter template
     * @param array<string, string> $info
     * @return string
     */
    protected function getSetterTemplate(array $info): string
    {
        $name = $info['name'];
        $type = $info['type'];
        $typeDockBlock = $type;
        $typeArg = $type;
        $required = $info['required'];
        if ($required === false) {
            $typeDockBlock .= '|null';
            $typeArg = '?' . $typeArg;
        }
        $cleanName = Str::snake($name, ' ');
        $setterName = 'set' . Str::ucfirst($name);

        return <<<EOF
        /**
            * Set the $cleanName value 
            * @param $typeDockBlock \$$name 
            * @return \$this
            */
           public function $setterName($typeArg \$$name): self
           {
               \$this->$name = \$$name;
                
               return \$this;
           }
           
           
        EOF;
    }

    /**
     * Return the getter template
     * @param array<string, string> $info
     * @return string
     */
    protected function getGetterTemplate(array $info): string
    {
        $name = $info['name'];
        $type = $info['type'];
        $typeDockBlock = $type;
        $typeReturn = $type;
        $required = $info['required'];
        if ($required === false) {
            $typeDockBlock .= '|null';
            $typeReturn = '?' . $typeReturn;
        }
        $cleanName = Str::snake($name, ' ');
        $getterName = 'get' . Str::ucfirst($name);

        return <<<EOF
        /**
            * Return the $cleanName value 
            * @return $typeDockBlock
            */
           public function $getterName(): $typeReturn
           {
               return \$this->$name;
           }
           
           
        EOF;
    }

    /**
     * Return the from entity body
     * @param string $content
     * @return string
     */
    protected function getFromEntityBody(string $content): string
    {
        $result = '';
        $templateEntity = '';
        if ($this->createInstanceFormEntity && !empty($this->fromEntityMaps)) {
            $templateEntity = '@template TEntity as Entity';
            $result = <<<EOF
            /**
                * @param TEntity \$entity
                * @return \$this
                */
               public function fromEntity(Entity \$entity): self
               {
                
            EOF;
            foreach ($this->fromEntityMaps as $property => $map) {
                $result .= <<<EOF
                \$this->$property = \$entity->$map;
                
            EOF;
            }

            $result .= <<<EOF
                
                   return \$this;
               }
            
            EOF;
        }

        $template = (string) str_replace('%from_entity%', $result, $content);

        return str_replace('%template_entity%', $templateEntity, $template);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPropertyTemplate(string $className, array $info): string
    {
        $name = $info['name'];
        $default = $info['default'];
        $type = $info['type'];
        $typeDockBlock = $type;
        $typeProp = $type;
        $required = $info['required'];
        if ($required === false) {
            $typeDockBlock .= '|null';
            $typeProp = '?' . $typeProp;
        }

        $cleanName = Str::snake($name, ' ');
        if ($default !== null) {
            $default = ' = ' . $default;
        } else {
            $default = '';
        }



        return <<<EOF
        /**
            * The $cleanName field
            * @var $typeDockBlock
            */
            protected $typeProp \$$name$default;
        
            
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        if (!$this->createInstanceFormEntity) {
            return '';
        }

        return <<<EOF
        use Platine\Orm\Entity; 
        
        EOF;
    }
}

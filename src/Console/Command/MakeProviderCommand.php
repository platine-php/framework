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
 *  @file MakeProviderCommand.php
 *
 *  The Make provider Command class
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

/**
 * @class MakeProviderCommand
 * @package Platine\Framework\Console\Command
 */
class MakeProviderCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'provider';

    /**
     * Whether to use boot method
     * @var bool
     */
    protected bool $useBoot = false;

    /**
     * Whether to add routes in the provider
     * @var bool
     */
    protected bool $addRoutes = false;

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
        $this->setName('make:provider')
               ->setDescription('Command to generate new service provider class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);
        $io = $this->io();

        $this->useBoot = $io->confirm('Use bootstrap feature ?', 'n');
        $this->addRoutes = $io->confirm('Add routes ?', 'n');
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
        
        use Platine\Framework\Service\ServiceProvider;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname% extends ServiceProvider
        {
            /**
            * {@inheritdoc}
            */
           public function register(): void
           {
             %method_body%
           }
            
            %boot_body%
            %add_routes_body%
        }
        
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        $useBoot = $this->getBootBody($content);

        return $this->getAddRoutesBody($useBoot);
    }

    /**
     * Return the boot method body
     * @param string $content
     * @return string
     */
    protected function getBootBody(string $content): string
    {
        $result = '';
        if ($this->useBoot) {
            $result = <<<EOF
            /**
                * {@inheritdoc}
                */
               public function boot(): void
               {
                    
               }
                
            EOF;
        }

        return str_replace('%boot_body%', $result, $content);
    }

    /**
     * Return the add routes method body
     * @param string $content
     * @return string
     */
    protected function getAddRoutesBody(string $content): string
    {
        $result = '';
        if ($this->addRoutes) {
            $result = <<<EOF
            /**
                * {@inheritdoc}
                */
               public function addRoutes(Router \$router): void
               {
                    
               }
                
            EOF;
        }

        return str_replace('%add_routes_body%', $result, $content);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        if (!$this->addRoutes) {
            return '';
        }

        return <<<EOF
        use Platine\Route\Router; 
        
        EOF;
    }
}

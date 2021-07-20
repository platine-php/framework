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
 *  @file HomeAction.php
 *
 *  The Home action class
 *
 *  @package    Platine\Framework\Demo\Action
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Action;

use Platine\Framework\Config\DbConfig;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Template\Template;

/**
 * @class HomeAction
 * @package Platine\Framework\Demo\Action
 * @template T
 */
class HomeAction implements RequestHandlerInterface
{
    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
     * The database configuration instance
     * @var DbConfig<T>
     */
    protected DbConfig $dbConfig;

    /**
     * Create new instance
     * @param Template $template
     * @param DbConfig $dbConfig
     */
    public function __construct(
        Template $template,
        DbConfig $dbConfig
    ) {
        $this->template = $template;
        $this->dbConfig = $dbConfig;
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //$name = $this->dbConfig->get('billing.invoice.tax');
        //$name = $this->dbConfig['tnh.foo.bar.one.two.three.four.name'];
        //$this->dbConfig->set('tnh.foo.bar.one.two.three.four.name', 'LONG');

        //var_dump($name);
        /*$debug = $this->dbConfig->get('app.debug');
        $prefix = $this->dbConfig->get('app.billing.invoice.prefix');
        echo $name.'|'.$debug.'|'.$prefix;
         *
         */
        //$configurations = $this->dbConfig->all();

        return new TemplateResponse(
            $this->template,
            'home',
            []
        );
    }
}

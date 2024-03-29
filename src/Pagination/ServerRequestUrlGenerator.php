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
 *  @file ServerRequestUrlGenerator.php
 *
 *  The Pagination URL Generator using ServerRequestInteface
 *
 *  @package    Platine\Framework\Pagination
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Pagination;

use Platine\Http\ServerRequestInterface;
use Platine\Pagination\UrlGeneratorInterface;

/**
 * @class ServerRequestUrlGenerator
 * @package Platine\Framework\Pagination
 */
class ServerRequestUrlGenerator implements UrlGeneratorInterface
{
    /**
     * The server request instance
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The name of variable or key to use
     * @var string
     */
    protected string $queryVarName = 'page';

    /**
     * The current pagination URL pattern
     * @var string
     */
    protected string $url = '';

    /**
     * Create new instance
     * @param ServerRequestInterface $request
     * @param string $queryName
     */
    public function __construct(ServerRequestInterface $request, string $queryName)
    {
        $this->request = $request;
        $this->queryVarName = $queryName;

        $this->determineUri();
    }

    /**
     * {@inheritdoc}
     */
    public function generatePageUrl(int $page): string
    {
        return str_replace('{num}', (string) $page, $this->url);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlPattern(): string
    {
        return $this->url;
    }

    /**
     * Determine the pagination URL
     * @return void
     */
    protected function determineUri(): void
    {
        $uri = $this->request->getUri();
        $queryString = $uri->getQuery();
        $page = $this->queryVarName;

        $query = '';
        if (empty($queryString)) {
            $query = sprintf('?%s={num}', $page);
        } else {
            $parts = explode($page . '=', $queryString);
            $count = count($parts);

            if ($count === 1) {
                $query = sprintf('?%s&%s={num}', $queryString, $page);
            } else {
                if (empty($parts[0])) {
                    $query = sprintf('?%s={num}', $page);
                } else {
                    $query = sprintf('?%s%s={num}', $parts[0], $page);
                }
            }
        }

        $currentUrl = (string) $uri;
        $parts = explode('?', $currentUrl);
        $query = $parts[0] . $query;
        $this->url = $query;
    }
}

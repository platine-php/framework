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
 *  @file BootstrapRenderer.php
 *
 *  The Pagination renderer using Bootstrap 4.* class
 *
 *  @package    Platine\Framework\Pagination
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Pagination;

use Platine\Pagination\Pagination;
use Platine\Pagination\RendererInterface;

/**
 * @class BootstrapRenderer
 * @package Platine\Framework\Pagination
 */
class BootstrapRenderer implements RendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(Pagination $pagination): string
    {
        if ($pagination->getTotalPages() <= 1) {
            return '';
        }

        $html = '<ul class = "pagination">';

        if ($pagination->hasPreviousPage()) {
            $html .= '<li class = "page-item"><a href = "'
                    . $pagination->getPreviousUrl() . '" class="page-link">&laquo; '
                    . $pagination->getPreviousText() . '</a></li>';
        }

        /** @var array<\Platine\Pagination\Page> $pages */
        $pages = $pagination->getPages();

        foreach ($pages as $page) {
            if ($page->getUrl() !== null) {
                $html .= '<li class = "page-item' . ($page->isCurrent() ? '  active"' : '"')
                      . '><a href = "' . $page->getUrl() . '" class="page-link">'
                      . $page->getNumber() . '</a></li>';
            } else {
                $html .= '<li class = "page-item disabled"><a href="#" class="page-link">'
                        . $page->getNumber() . '</a></li>';
            }
        }

        if ($pagination->hasNextPage()) {
            $html .= '<li class = "page-item"><a href = "'
                    . $pagination->getNextUrl() . '" class="page-link">'
                    . $pagination->getNextText() . ' &raquo;</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }
}

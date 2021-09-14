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
 *  @file FileResponse.php
 *
 *  The File Send Response class
 *
 *  @package    Platine\Framework\Http\Response
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Response;

use Platine\Http\Response;
use Platine\Http\Stream;
use Platine\Stdlib\Helper\Path;

/**
 * @class FileResponse
 * @package Platine\Framework\Http\Response
 */
class FileResponse extends Response
{

    /**
     * Create new instance
     * @param string $path
     * @param string|null $filename
     */
    public function __construct(string $path, ?string $filename = null)
    {
        parent::__construct(200);

        $realpath = Path::realPath($path);
        $extension = pathinfo($realpath, PATHINFO_EXTENSION);
        $mimetype = 'application/octet-stream';
        if (!empty($extension)) {
            $mimetype = Path::getMimeByExtension($extension);
        }

        if (empty($filename)) {
            $filename = basename($realpath);
        }

        $body = new Stream($realpath);

        $this->headers['content-description'] = ['File Transfer'];
        $this->headers['content-type'] = [$mimetype];
        $this->headers['content-disposition'] = ['attachment; filename="' . $filename  . '"'];
        $this->headers['expires'] = ['0'];
        $this->headers['cache-control'] = ['must-revalidate'];
        $this->headers['pragma'] = ['public'];
        $this->headers['content-length'] = [(string) $body->getSize()];

        $this->body = $body;
    }
}

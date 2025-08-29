<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant PHP
 * Web framework
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

declare(strict_types=1);

namespace Platine\Framework\Handler\Error;

use Platine\Framework\Http\Exception\HttpMethodNotAllowedException;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Http\ResponseInterface;

/**
 * @class RestErrorHandler
 * @package Platine\Framework\Handler\Error
 */
class RestErrorHandler extends ErrorHandler
{
    /**
     * {@inheritodc}
     */
    protected function errorResponse(): ResponseInterface
    {
        $extras = [];
        if ($this->detail) {
            $extras['exception'] = [
                'type' => get_class($this->exception),
                'code' => $this->exception->getCode(),
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
            ];
        }
        $response = new RestResponse(
            [],
            $extras,
            false,
            $this->exception->getCode(),
            $this->exception->getMessage(),
            $this->statusCode
        );
        if (
            !empty($this->contentType)
            && array_key_exists($this->contentType, $this->renderers)
        ) {
            $response = $response->withHeader('Content-Type', $this->contentType);
        }

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $allowMethods = implode(', ', $this->exception->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowMethods);
        }

        return $response;
    }
}

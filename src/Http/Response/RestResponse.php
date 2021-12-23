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
 *  @file RestResponse.php
 *
 *  This REST Response class
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

/**
 * @class RestResponse
 * @package Platine\Framework\Http\Response
 */
class RestResponse extends JsonResponse
{
    /**
     * Create new instance
     * @param mixed $data
     * @param array<string, mixed> $extras
     * @param bool $success
     * @param int $code
     * @param string $message
     * @param int $statusCode
     * @param string $reasonPhrase
     */
    public function __construct(
        $data = [],
        array $extras = [],
        bool $success = true,
        int $code = 0,
        string $message = '',
        int $statusCode = 200,
        string $reasonPhrase = ''
    ) {
        $result = [
            'success' => $success,
            'timestamp' => time(),
            'code' => $code,
        ];

        if (!empty($message)) {
            $result['message'] = $message;
        }

        $result['data'] = $data;

        if (!empty($extras)) {
            $result = array_merge($result, $extras);
        }

        parent::__construct($result, $statusCode, $reasonPhrase);
    }
}

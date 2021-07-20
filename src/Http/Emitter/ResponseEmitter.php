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
 * Copyright (c) 2020 Evgeniy Zyubin
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
 *  @file ResponseEmitter.php
 *
 *  The default response emitter class
 *
 *  @package    Platine\Framework\Http\Emitter
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Emitter;

use InvalidArgumentException;
use Platine\Framework\Http\Emitter\Exception\HeadersAlreadySentException;
use Platine\Framework\Http\Emitter\Exception\OutputAlreadySentException;
use Platine\Http\ResponseInterface;
use Platine\Http\StreamInterface;

/**
 * @class ResponseEmitter
 * @package Platine\Framework\Http\Emitter
 */
class ResponseEmitter implements EmitterInterface
{

    /**
     * The response before length
     * @var int|null
     */
    protected ?int $bufferLength = null;

    /**
     * Create new instance
     * @param int|null $bufferLength
     */
    public function __construct(?int $bufferLength = null)
    {
        if ($bufferLength !== null && $bufferLength < 1) {
            throw new InvalidArgumentException(sprintf(
                'The response buffer length must be greater than zero; received [%d].',
                $bufferLength
            ));
        }

        $this->bufferLength = $bufferLength;
    }

    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response, bool $body = true): void
    {
        if (headers_sent()) {
            throw HeadersAlreadySentException::create();
        }

        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw OutputAlreadySentException::create();
        }

        $this->emitHeaders($response);
        $this->emitStatusLine($response);

        if ($body && $response->getBody()->isReadable()) {
            $this->emitBody($response);
        }
    }

    /**
     * Emit headers
     * @param ResponseInterface $response
     * @return void
     */
    protected function emitHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(
                ' ',
                '-',
                ucwords(strtolower(str_replace('-', ' ', $name)))
            );

            $isFirst = $name !== 'Set-Cookie';
            foreach ($values as $value) {
                $header = sprintf('%s: %s', $name, $value);
                header($header, $isFirst);
                $isFirst = false;
            }
        }
    }

    /**
     * Emit status line
     * @param ResponseInterface $response
     * @return void
     */
    protected function emitStatusLine(ResponseInterface $response): void
    {
        $code = $response->getStatusCode();
        $text = $response->getReasonPhrase();
        $protocolVersion = $response->getProtocolVersion();
        $status = $code . (!$text ? '' : ' ' . $text);
        $header = sprintf('HTTP/%s %s', $protocolVersion, $status);
        header($header, true, $code);
    }

    /**
     * Emit body
     * @param ResponseInterface $response
     * @return void
     */
    protected function emitBody(ResponseInterface $response): void
    {
        if ($this->bufferLength === null) {
            echo $response->getBody();
            return;
        }

        flush();
        $body = $response->getBody();
        $range = $this->parseContentRange(
            $response->getHeaderLine('content-range')
        );

        if (isset($range['unit']) && $range['unit'] === 'bytes') {
            $this->emitBodyRange($body, $range['first'], $range['last']);
            return;
        }

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            echo $body->read($this->bufferLength);
        }
    }

    /**
     * Emit body range
     * @param StreamInterface $body
     * @param int $first
     * @param int $last
     * @return void
     */
    protected function emitBodyRange(StreamInterface $body, int $first, int $last): void
    {
        $length = $last - $first + 1;
        if ($body->isSeekable()) {
            $body->seek($first);
        }

        while ($length >= $this->bufferLength && !$body->eof()) {
            $contents = $body->read($this->bufferLength);
            $length -= strlen($contents);

            echo $contents;
        }

        if ($length > 0 && !$body->eof()) {
            echo $body->read($length);
        }
    }

    /**
     * Parser content range header
     * @param string $header
     * @return array<string, mixed>
     */
    protected function parseContentRange(string $header): array
    {
        $matches = [];

        if (
            preg_match(
                '/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/',
                $header,
                $matches
            )
        ) {
            return [
                'unit' => $matches['unit'],
                'first' => (int) $matches['first'],
                'last' => (int) $matches['last'],
                'length' => ($matches['length'] === '*') ? '*' : (int) $matches['length'],
            ];
        }

        return [];
    }
}

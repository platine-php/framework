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

/**
 *  @file BodyParserMiddleware.php
 *
 *  This middleware is used to parse the request body like application/json
 *
 *  @package    Platine\Framework\Http\Middleware
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Middleware;

use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Stdlib\Helper\Json;
use RuntimeException;

/**
 * @class BodyParserMiddleware
 * @package Platine\Framework\Http\Middleware
 */
class BodyParserMiddleware implements MiddlewareInterface
{

    /**
     * List of body parser to use
     * @var array<string, callable>
     */
    protected array $parsers = [];

    /**
     * Create new instance
     */
    public function __construct()
    {
        $this->registerDefaultParsers();
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $parsedBody = $request->getParsedBody();
        if ($parsedBody === null || empty($parsedBody)) {
            $parsedBody = $this->parseBody($request);

            $request = $request->withParsedBody($parsedBody);
        }

        return $handler->handle($request);
    }

    /**
     * Register the parser for the given content type
     * @param string $contentType
     * @param callable $parser
     * @return $this
     */
    public function registerParser(string $contentType, callable $parser): self
    {
        $this->parsers[$contentType] = $parser;

        return $this;
    }

    /**
     * Whether the given content type have a registered parser
     * @param string $contentType
     * @return bool
     */
    public function hasParser(string $contentType): bool
    {
        return isset($this->parsers[$contentType]);
    }

    /**
     * Return the parser for the given content type
     * @param string $contentType
     * @return callable
     * @throws RuntimeException
     */
    public function getParser(string $contentType): callable
    {
        if (!isset($this->parsers[$contentType])) {
            throw new RuntimeException(sprintf(
                'Can not find the parser for the given content type [%s]',
                $contentType
            ));
        }

        return $this->parsers[$contentType];
    }

    /**
     * Register the default body parser
     * @return void
     */
    protected function registerDefaultParsers(): void
    {
        $this->registerParser('application/json', static function ($body) {
            return Json::decode($body, true);
        });

        $this->registerParser('application/x-www-form-urlencoded', static function ($body) {
            $data = [];
            parse_str($body, $data);

            return $data;
        });
    }

    /**
     * Parse the body
     * @param ServerRequestInterface $request
     * @return null|array<mixed>object
     */
    protected function parseBody(ServerRequestInterface $request)
    {
        $contentType = $this->getRequestContentType($request);
        if ($contentType === null) {
            return null;
        }

        if (!$this->hasParser($contentType)) {
             // If not, look for a media type with a structured syntax suffix (RFC 6839)
            $parts = expldoe('+', $contentType);

            if (count($parts) >= 2) {
                $contentType = 'application/' . $parts[count($parts) - 1];
            }
        }

        if ($this->hasParser($contentType)) {
            $body = (string) $request->getBody();
            $parser = $this->getParser($contentType);

            $parsed = $parser($body);
            if (!is_null($parsed) && !is_array($parsed) && !is_object($parsed)) {
                throw new RuntimeException(sprintf(
                    'The return value of body parser must be an array, an object, or null, got [%s]',
                    gettype($parsed)
                ));
            }

            return $parsed;
        }

        return null;
    }

    /**
     * Return the request content type
     * @param ServerRequestInterface $request
     * @return string|null
     */
    protected function getRequestContentType(ServerRequestInterface $request): ?string
    {
        $contentTypes = $request->getHeader('Content-Type');
        $contentType = $contentTypes[0] ?? null;

        if (is_string($contentType) && trim($contentType) !== '') {
            $parts = explode(';', $contentType);

            return strtolower(trim($parts[0]));
        }

        return null;
    }
}

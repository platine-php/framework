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
 * Copyright (c) 2011 - 2017 rehyved.com
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
 *  @file HttpStatus.php
 *
 *  The Http Status class
 *
 * An enumeration of possible HTTP status codes.
 * This class provides convenience methods to check the type
 * of status and retrieve the reason phrase for the status.
 *
 *  @package    Platine\Framework\Http\Client
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Client;

use InvalidArgumentException;

/**
 * @class HttpStatus
 * @package Platine\Framework\Http\Client
 */
class HttpStatus
{
    // Informational 1xx:
    public const INFORMATIONAL = 100;
    public const CONTINUE = 100;
    public const SWITCHING_PROTOCOLS = 101;

    // Successful 2xx:
    public const SUCCESSFUL = 200;
    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NON_AUTHORITATIVE_INFORMATION = 203;
    public const NO_CONTENT = 204;
    public const RESET_CONTENT = 205;
    public const PARTIAL_CONTENT = 206;

    // Redirection 3xx:
    public const REDIRECTION = 300;
    public const MULTIPLE_CHOICES = 300;
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const SEE_OTHER = 303;
    public const NOT_MODIFIED = 304;
    public const USE_PROXY = 305;
    // Code 306 was used in a previous HTTP specification but no longer used but kept reserved.
    public const TEMPORARY_REDIRECT = 307;

    // Client Error 4xx:
    public const CLIENT_ERROR = 400;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const PAYMENT_REQUIRED = 402;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    public const REQUEST_TIMEOUT = 408;
    public const CONFLICT = 409;
    public const GONE = 410;
    public const LENGTH_REQUIRED = 411;
    public const PRECONDITION_FAILED = 412;
    public const REQUEST_ENTITY_TOO_LARGE = 413;
    public const REQUEST_URI_TOO_LONG = 414;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const EXPECTATION_FAILED = 417;

    // Server Error 5xx:
    public const SERVER_ERROR = 500;
    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
    public const GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const SERVER_ERROR_END = 600;

    public const REASON_PHRASES = [
        self::CONTINUE => 'Continue',
        self::SWITCHING_PROTOCOLS => 'Switching Protocols',

        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::NO_CONTENT => 'No Content',
        self::RESET_CONTENT => 'Reset Content',
        self::PARTIAL_CONTENT => 'Partial Content',

        self::MULTIPLE_CHOICES => 'Multiple Choices',
        self::MOVED_PERMANENTLY => 'Moved Permanently',
        self::FOUND => 'Found',
        self::SEE_OTHER => 'See Other',
        self::NOT_MODIFIED => 'Not Modified',
        self::USE_PROXY => 'Use Proxy',
        self::TEMPORARY_REDIRECT => 'Temporary Redirect',

        self::BAD_REQUEST => 'Bad Request',
        self::UNAUTHORIZED => 'Unauthorized',
        self::PAYMENT_REQUIRED => 'Payment Required',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::NOT_ACCEPTABLE => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT => 'Request Time-out',
        self::CONFLICT => 'Conflict',
        self::GONE => 'Gone',
        self::LENGTH_REQUIRED => 'Length Required',
        self::PRECONDITION_FAILED => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED => 'Expectation Failed',

        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::NOT_IMPLEMENTED => 'Not Implemented',
        self::BAD_GATEWAY => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported'
    ];

    /**
     * Checks if the provided status code falls in the Informational range of HTTP statuses
     * @param int $statusCode the status code to check
     * @return bool TRUE if it falls into the Informational range, FALSE if not
     */
    public static function isInformational(int $statusCode): bool
    {
        return ($statusCode >= self::INFORMATIONAL) && ($statusCode < self::SUCCESSFUL);
    }

    /**
     * Checks if the provided status code falls in the Successful range of HTTP statuses
     * @param int $statusCode the status code to check
     * @return bool TRUE if it falls into the Successful range, FALSE if not
     */
    public static function isSuccessful(int $statusCode): bool
    {
        return ($statusCode >= self::SUCCESSFUL) && ($statusCode < self::REDIRECTION);
    }

    /**
     * Checks if the provided status code falls in the Redirection range of HTTP statuses
     * @param int $statusCode the status code to check
     * @return bool TRUE if it falls into the Redirection range, FALSE if not
     */
    public static function isRedirection(int $statusCode): bool
    {
        return ($statusCode >= self::REDIRECTION) && ($statusCode < self::CLIENT_ERROR);
    }

    /**
     * Checks if the provided status code falls in the Client error range of HTTP statuses
     * @param int $statusCode the status code to check
     * @return bool TRUE if it falls into the Client error range, FALSE if not
     */
    public static function isClientError(int $statusCode): bool
    {
        return ($statusCode >= self::CLIENT_ERROR) && ($statusCode < self::SERVER_ERROR);
    }

    /**
     * Checks if the provided status code falls in the Server error range of HTTP statuses
     * @param int $statusCode the status code to check
     * @return bool TRUE if it falls into the Server error range, FALSE if not
     */
    public static function isServerError(int $statusCode): bool
    {
        return ($statusCode >= self::SERVER_ERROR) && ($statusCode < self::SERVER_ERROR_END);
    }

    /**
     * Checks if the provided status code falls in the Client or Server error range of HTTP statuses
     * @param int $statusCode the status code to check
     * @return bool TRUE if it falls into the Client or Server error range, FALSE if not
     */
    public static function isError(int $statusCode): bool
    {
        return ($statusCode >= self::CLIENT_ERROR) && ($statusCode < self::SERVER_ERROR_END);
    }

    /**
     * Retrieve the reason phrase for the provided HTTP status code
     * @param int $statusCode the status code for which to retrieve the reason phrase
     * @return string the reason phrase
     * @throws InvalidArgumentException if the provided value is not a valid HTTP status code
     */
    public static function getReasonPhrase(int $statusCode): string
    {
        if (array_key_exists($statusCode, self::REASON_PHRASES)) {
            return self::REASON_PHRASES[$statusCode];
        }
        throw new InvalidArgumentException('Invalid status code');
    }
}

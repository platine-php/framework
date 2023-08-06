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
 * Copyright (c) 2017 Jitendra Adhikari
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
 *  @file Loader.php
 *
 *  The Environment Loader class
 *
 *  @package    Platine\Framework\Env
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Env;

use InvalidArgumentException;
use RuntimeException;

/**
 * @class Loader
 * @package Platine\Framework\Env
 */
class Loader
{
    /**
     * Put the parsed key value pair into
     * $_ENV super global.
     */
    public const ENV = 1;

    /**
     * Put the parsed key value pair into "putenv()".
     */
    public const PUTENV = 2;

    /**
     * Put the parsed key value pair into
     * $_SERVER super global.
     */
    public const SERVER = 4;

    /**
     * Put the parsed key value pair into all of the sources.
     */
    public const ALL = 7;

    /**
     * Loads environment file and puts the key value pair
     * in one or all of putenv()/$_ENV/$_SERVER.
     * @param string $file
     * @param bool $overwrite
     * @param int $mode
     * @return void
     */
    public function load(
        string $file,
        bool $overwrite = false,
        int $mode = self::PUTENV
    ): void {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf(
                'The [%s] file does not exist or is not readable',
                $file
            ));
        }

        $fileContent = (string) file_get_contents($file);
        // Get file contents, fix the comments and parse as ini.
        $content = (string) preg_replace('/^\s*#/m', ';', $fileContent);
        $parsed  = parse_ini_string($content, false, INI_SCANNER_RAW);

        if ($parsed === false) {
            throw new RuntimeException(sprintf(
                'The [%s] file cannot be parsed due to malformed values',
                $file
            ));
        }

        $this->setValues($parsed, $overwrite, $mode);
    }

    /**
     * Set the environment values from given variables.
     * @param array<string, mixed> $vars
     * @param bool $overwrite
     * @param int $mode
     * @return void
     */
    protected function setValues(
        array $vars,
        bool $overwrite,
        int $mode
    ): void {
        $default = microtime(true);
        foreach ($vars as $key => $value) {
            // Skip if we already have value and cant override.
            if (!$overwrite && $default !== Env::get($key, $default)) {
                continue;
            }

            $this->setValue($key, $value, $mode);
        }

        $this->resolveReferences($vars, $mode);
    }

    /**
     * Set environment value for the given key
     * @param string $key
     * @param string $value
     * @param int $mode
     * @return void
     */
    protected function setValue(string $key, string $value, int $mode): void
    {
        $this->setValueEnv($key, $value, $mode);
        $this->setValueServer($key, $value, $mode);
        $this->setValuePutenv($key, $value, $mode);
    }

    /**
     * Resolve variable references like MY_KEY=${VAR_NAME}
     * @param array<string, mixed> $vars
     * @param int $mode
     * @return void
     */
    protected function resolveReferences(
        array $vars,
        int $mode
    ): void {
        foreach ($vars as $key => $value) {
            if (!$value || strpos($value, '${') === false) {
                continue;
            }

            $value = preg_replace_callback('~\$\{(\w+)\}~', function ($m) {
                return (null === $ref = Env::get($m[1], null)) ? $m[0] : $ref;
            }, $value);

            $this->setValue($key, $value, $mode);
        }
    }

    /**
     * Set environment value for the given key to $_ENV
     * @param string $key
     * @param string $value
     * @param int $mode
     * @return void
     */
    protected function setValueEnv(string $key, string $value, int $mode): void
    {
        if ($mode & self::ENV) {
            $_ENV[$key] = $value;
        }
    }

    /**
     * Set environment value for the given key to $_SERVER
     * @param string $key
     * @param string $value
     * @param int $mode
     * @return void
     */
    protected function setValueServer(string $key, string $value, int $mode): void
    {
        if ($mode & self::SERVER) {
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Set environment value for the given key to "putenv()"
     * @param string $key
     * @param string $value
     * @param int $mode
     * @return void
     */
    protected function setValuePutenv(string $key, string $value, int $mode): void
    {
        if ($mode & self::PUTENV) {
            putenv("$key=$value");
        }
    }
}

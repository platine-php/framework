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
 *  @file HMAC.php
 *
 *  The Signer using HMAC
 *
 *  @package    Platine\Framework\Security\JWT\Signer
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\JWT\Signer;

use Platine\Config\Config;
use Platine\Framework\Security\JWT\Exception\InvalidAlgorithmException;
use Platine\Framework\Security\JWT\SignerInterface;

/**
 * @class HMAC
 * @package Platine\Framework\Security\JWT\Signer
 * @template T
 */
class HMAC implements SignerInterface
{
    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The algorithm to use
     * @var string
     */
    protected string $algo;

    /**
     * Create new instance
     * @param Config<T> $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $algo = $config->get('api.sign.hmac.signature_algo', '');
        if (!in_array($algo, hash_hmac_algos())) {
            throw new InvalidAlgorithmException(sprintf(
                'Invalid HMAC algorithm [%s]',
                $algo
            ));
        }

        $this->algo = $algo;
    }


    /**
     * {@inheritdoc}
     */
    public function sign(string $data, string $key): string
    {
        return hash_hmac($this->algo, $data, $key, true);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $key, string $signature, string $data): bool
    {
        $signed = $this->sign($data, $key);

        return $this->hashEquals($signed, $signature);
    }

    /**
     * {@inheritdoc}
     */
    public function getSignatureAlgo(): string
    {
        return $this->algo;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenAlgoName(): string
    {
        return $this->config->get('api.sign.hmac.token_header_algo', '');
    }

    /**
     * Test if two hash is equals
     * @param string $signature
     * @param string $data
     * @return bool
     */
    protected function hashEquals(string $signature, string $data): bool
    {
        return hash_equals($signature, $data);
    }
}

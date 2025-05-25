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
 * Copyright (c) 2024 Wildy Sheverando
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
 *  @file TOTP.php
 *
 *  The TOTP class
 *
 *  @package    Platine\Framework\Security\OTP
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\OTP;

use Platine\Stdlib\Helper\Str;

/**
 * @class TOTP
 * @package Platine\Framework\Security\OTP
 */
class TOTP
{
    /**
     * The length of the secret
     * @var int
     */
    protected int $secretLength = 16;

    /**
     * The time step to be used
     * @var int
     */
    protected int $timeStep = 30;

    /**
     * The supported digit length
     * @var int
     */
    protected int $digit = 6;

    /**
     * The secret key to use
     * @var string
     */
    protected string $secret;

    /**
     * Create new instance
     * @param string|null $secret
     */
    public function __construct(?string $secret = null)
    {
        if ($secret === null) {
            $secret = $this->generateSecret();
        }

        $this->secret = $secret;
    }

    /**
     * Return the current code (auth)
     * @param string|null $secret
     * @return string
     */
    public function getCode(?string $secret = null): string
    {
        if ($secret === null) {
            $secret = $this->secret;
        }

        /*
            how it's work ?
            1. Decode secret key from Base32
            2. Count time step
            3. Pack counter time to binary strings.
            4. Hashing the timehex and secret to sha1
            5. Get offset from hash
            6. Generate binary code
            7. Convert it to strings.
        */
        $secretKey = $this->base32Decode($secret);
        $timeCounter = floor(time() / $this->timeStep);
        $timeHex = pack('N*', 0) . pack('N*', $timeCounter);
        // TODO: use support to set custom algorithm
        $hash = hash_hmac('sha1', $timeHex, $secretKey, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0xF;

        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % pow(10, $this->digit);

        return Str::padLeft((string) $otp, $this->digit, '0');
    }

    /**
     * Verify the given code
     * @param string $code
     * @param string|null $secret
     * @return bool
     */
    public function verify(string $code, ?string $secret = null): bool
    {
        return $this->getCode($secret) === $code;
    }

    /**
     * Return the URL to be used to generated QR Code or import in authenticator app
     * @param string $label
     * @param string $issuer
     * @return string
     */
    public function getURL(string $label, string $issuer = ''): string
    {
        $secret = $this->getSecret();
        $labelEncode = rawurlencode($label);
        $issuerEncode = rawurlencode($issuer);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s',
            $labelEncode,
            $secret,
            $issuerEncode
        );
    }

    /**
     * Generate the secret key
     * @return string
     */
    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456';
        $length = strlen($chars) - 1;
        $random = '';
        for ($i = 0; $i < $this->secretLength; $i++) {
            $random .= $chars[random_int(0, $length)];
        }

        return $random;
    }

    /**
     * Return the secret length
     * @return int
     */
    public function getSecretLength(): int
    {
        return $this->secretLength;
    }

    /**
     * Return the time step
     * @return int
     */
    public function getTimeStep(): int
    {
        return $this->timeStep;
    }

    /**
     * Return the code supported digit
     * @return int
     */
    public function getDigit(): int
    {
        return $this->digit;
    }

    /**
     * Return the secret
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Set the secret length
     * @param int $secretLength
     * @return $this
     */
    public function setSecretLength(int $secretLength): self
    {
        $this->secretLength = $secretLength;
        return $this;
    }

    /**
     * Set the time step
     * @param int $timeStep
     * @return $this
     */
    public function setTimeStep(int $timeStep): self
    {
        $this->timeStep = $timeStep;
        return $this;
    }

    /**
     * Set the code supported digit
     * @param int $digit
     * @return $this
     */
    public function setDigit(int $digit): self
    {
        $this->digit = $digit;
        return $this;
    }

    /**
     * Set the secret
     * @param string $secret
     * @return $this
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Base32 decoding
     * @param string $str
     * @return string
     */
    protected function base32Decode(string $str): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456';
        $string = Str::upper($str);
        $length = Str::length($string);

        $n = 0;
        $j = 0;
        $binary = '';
        for ($i = 0; $i < $length; $i++) {
            $n = $n << 5;
            $n = $n + strpos($chars, $string[$i]);
            $j += 5;

            if ($j >= 8) {
                $j -= 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }
}

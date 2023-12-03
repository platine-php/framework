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
 *  @file JWT.php
 *
 *  The JWT class
 *
 *  @package    Platine\Framework\Security\JWT
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\JWT;

use DateTime;
use Platine\Framework\Security\JWT\Encoder\Base64UrlSafeEncoder;
use Platine\Framework\Security\JWT\Exception\InvalidTokenException;
use Platine\Framework\Security\JWT\Exception\JWTException;
use Platine\Framework\Security\JWT\Exception\TokenExpiredException;

/**
 * @class JWT
 * @package Platine\Framework\Security\JWT
 */
class JWT
{
    /**
     * The payload
     * @var array<string, mixed>
     */
    protected array $payload = [];

    /**
     * The JWT headers
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * The encoder instance
     * @var EncoderInterface
     */
    protected EncoderInterface $encoder;

    /**
     * The signer instance
     * @var SignerInterface
     */
    protected SignerInterface $signer;

    /**
     * The signature
     * @var string
     */
    protected string $signature;

    /**
     * The encoded signature
     * @var string
     */
    protected string $encodedSignature;

    /**
     * Whether already generate signature
     * @var bool
     */
    protected bool $signed = false;

    /**
     * The original JWT token
     * @var string
     */
    protected string $originalToken;

    /**
     * The secret key to used to sign the token
     * @var string
     */
    protected string $secret = '';

    /**
     * Create new instance
     * @param SignerInterface $signer
     * @param EncoderInterface|null $encoder
     */
    public function __construct(
        SignerInterface $signer,
        ?EncoderInterface $encoder = null
    ) {
        $this->encoder = $encoder ?? new Base64UrlSafeEncoder();
        $this->signer = $signer;
    }

    /**
     * Decode the JWT instance based on the given token
     * @param string $token
     * @return $this
     */
    public function decode(string $token): self
    {
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $headers = json_decode($this->encoder->decode($parts[0]), true);
            $payload = json_decode($this->encoder->decode($parts[1]), true);
            if (is_array($headers) && is_array($payload)) {
                $algo = $headers['alg'] ?? '';
                if (empty($algo) || $algo !== $this->signer->getTokenAlgoName()) {
                    throw new InvalidTokenException(sprintf(
                        'The token [%s] cannot be validated, missing or invalid algorithm',
                        $token
                    ));
                } else {
                    $this->setHeaders($headers)
                          ->setPayload($payload)
                           ->setOriginalToken($token)
                            ->setEncodedSignature($parts[2]);

                    if (!$this->verify()) {
                        throw new InvalidTokenException(sprintf(
                            'The token [%s] cannot be verified because it is invalid',
                            $token
                        ));
                    }

                    if ($this->isExpired()) {
                        throw new TokenExpiredException(sprintf(
                            'The token [%s] is already expired',
                            $token
                        ));
                    }

                    return $this;
                }
            }
        }

        throw new InvalidTokenException(sprintf(
            'The token [%s] using an invalid JWT format',
            $token
        ));
    }

    /**
     * Verifies that the internal input signature corresponds to the encoded
     * signature previously stored (@see $this::load).
     * @return bool
     */
    public function verify(): bool
    {
        if (empty($this->secret) || empty($this->headers['alg'])) {
            return false;
        }

        $decodedSignature = $this->encoder->decode($this->getEncodedSignature());
        $tokenSignature = $this->getTokenSignature();

        return $this->signer->verify($this->secret, $decodedSignature, $tokenSignature);
    }

    /**
     * Get the original token signature if it exists, otherwise generate the
     * signature input for the current instance
     * @return string
     */
    public function getTokenSignature(): string
    {
        $parts = explode('.', $this->originalToken);

        if (count($parts) >= 2) {
            return sprintf('%s.%s', $parts[0], $parts[1]);
        }

        return $this->generateSignature();
    }

    /**
     * Sign the data
     * @return string
     */
    public function sign(): string
    {
        $this->signature = $this->signer->sign(
            $this->generateSignature(),
            $this->secret
        );
        $this->signed = true;

        return $this->signature;
    }

    /**
     * Return the signature. Note you need call ::sign first before
     * call this method
     * @return string
     */
    public function getSignature(): string
    {
        if ($this->signed) {
            return $this->signature;
        }

        throw new JWTException('The data is not yet signed, please sign it first');
    }

    /**
     * Whether already signed or not
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->signed;
    }

    /**
     * Return the JWT token
     * @return string
     */
    public function getToken(): string
    {
        $signature = $this->generateSignature();

        return sprintf(
            '%s.%s',
            $signature,
            $this->encoder->encode($this->getSignature())
        );
    }

    /**
     * Generate the signature
     * @return string
     */
    public function generateSignature(): string
    {
        $this->setDefaults();

        $encodedPayload = $this->encoder->encode((string) json_encode(
            $this->getPayload(),
            JSON_UNESCAPED_SLASHES
        ));

        $encodedHeaders = $this->encoder->encode((string) json_encode(
            $this->getHeaders(),
            JSON_UNESCAPED_SLASHES
        ));

        return sprintf('%s.%s', $encodedHeaders, $encodedPayload);
    }

    /**
     * Whether to current token is valid
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->verify() && !$this->isExpired();
    }

    /**
     * Whether the token already expired
     * @return bool
     */
    public function isExpired(): bool
    {
        if (isset($this->payload['exp'])) {
            $exp = $this->payload['exp'];
            $now = new DateTime('now');

            if (is_int($exp)) {
                return ($now->getTimestamp() - $exp) > 0;
            }

            if (is_numeric($exp)) {
                return ((float) $now->format('U') - $exp) > 0;
            }
        }

        return false;
    }

    /**
     * Return the encoded signature
     * @return string
     */
    public function getEncodedSignature(): string
    {
        return $this->encodedSignature;
    }

    /**
     * Return the encoded signature
     * @param string $encodedSignature
     * @return $this
     */
    public function setEncodedSignature(string $encodedSignature): self
    {
        $this->encodedSignature = $encodedSignature;
        return $this;
    }


    /**
     * Get the original JWT token
     * @return string
     */
    public function getOriginalToken(): string
    {
        return $this->originalToken;
    }

    /**
     * Set the original JWT token
     * @param string $originalToken
     * @return $this
     */
    public function setOriginalToken(string $originalToken): self
    {
        $this->originalToken = $originalToken;
        return $this;
    }

    /**
     * Return the payload
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Return the JWT headers
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the encoder used
     * @return EncoderInterface
     */
    public function getEncoder(): EncoderInterface
    {
        return $this->encoder;
    }

    /**
     * Return the signer
     * @return SignerInterface
     */
    public function getSigner(): SignerInterface
    {
        return $this->signer;
    }

    /**
     * Set the payload
     * @param array<string, mixed> $payload
     * @return $this
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Set the headers
     * @param array<string, mixed> $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set the encoder
     * @param EncoderInterface $encoder
     * @return $this
     */
    public function setEncoder(EncoderInterface $encoder): self
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * Set the signer
     * @param SignerInterface $signer
     * @return $this
     */
    public function setSigner(SignerInterface $signer): self
    {
        $this->signer = $signer;
        return $this;
    }

    /**
     * Return the secret key
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Set the secret key
     * @param string $secret
     * @return $this
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }


    /**
     * Set default values for headers and payload
     * @return void
     */
    protected function setDefaults(): void
    {
        if (!isset($this->headers['typ'])) {
            $this->headers['typ'] = 'JWT';
        }

        if (!isset($this->headers['alg'])) {
            $this->headers['alg'] = $this->signer->getTokenAlgoName();
        }

        if (!isset($this->payload['iat'])) {
            $this->payload['iat'] = time();
        }
    }
}

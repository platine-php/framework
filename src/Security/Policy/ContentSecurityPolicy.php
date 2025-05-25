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
 * Copyright (c) 2015 - 2023 Paragon Initiative Enterprises
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
 *  @file ContentSecurityPolicy.php
 *
 *  The Content Security Policy class
 *
 *  @package    Platine\Framework\Security\Policy
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\Policy;

/**
 * @class ContentSecurityPolicy
 * @package Platine\Framework\Security\Policy
 */
class ContentSecurityPolicy extends AbstractPolicy
{
    /**
     * Content Security Policy white list directives.
     * @var array<string, bool>
     */
    protected array $whitelist = [
        'base-uri' => true,
        'child-src' => true,
        'connect-src' => true,
        'default-src' => true,
        'font-src' => true,
        'form-action' => true,
        'frame-ancestors' => true,
        'frame-src' => true,
        'img-src' => true,
        'manifest-src' => true,
        'media-src' => true,
        'navigate-to' => true,
        'object-src' => true,
        'prefetch-src' => true,
        'script-src' => true,
        'script-src-attr' => true,
        'script-src-elem' => true,
        'style-src' => true,
        'style-src-attr' => true,
        'style-src-elem' => true,
        'worker-src' => true,
    ];

    /**
     * {@inheritdoc}
     */
    public function headers(): string
    {
        $headers = [
            $this->directives(),
            $this->pluginTypes(),
            $this->sandbox(),
            $this->requireTrustedTypesFor(),
            $this->trustedTypes(),
            $this->blockAllMixedContent(),
            $this->upgradeInsecureRequests(),
            $this->reportTo(),
            $this->reportUri(),
        ];

        return $this->implode(array_filter($headers), '; ');
    }

    /**
     * Build directive
     * @param array<string, mixed> $config
     * @return string
     */
    public function directive(array $config): string
    {
        if ($config['none'] ?? false) {
            return '\'none\'';
        }

        $sources = array_merge(
            $this->keywords($config),
            $this->schemes($config['schemes'] ?? []),
            $this->hashes($config['hashes'] ?? []),
            $this->nonces($config['nonces'] ?? []),
            $config['allow'] ?? []
        );

        $filtered = array_filter($sources);

        return $this->implode($filtered);
    }

    /**
     * Build directive keywords.
     * @param array<string, mixed> $config
     * @return array<string>
     */
    public function keywords(array $config): array
    {
        $whitelist = [
            'self' => true,
            'unsafe-inline' => true,
            'unsafe-eval' => true,
            'unsafe-hashes' => true,
            'strict-dynamic' => true,
            'report-sample' => true,
            'unsafe-allow-redirects' => true,
        ];

        $filtered = $this->filter($config, $whitelist);

        return array_map(function (string $keyword) {
            return sprintf('\'%s\'', $keyword);
        }, $filtered);
    }

    /**
     * Build directive schemes
     * @param array<string, mixed> $schemes
     * @return array<string>
     */
    public function schemes(array $schemes): array
    {
        return array_map(function (string $scheme) {
            $clean = trim($scheme);

            if (substr($clean, -1) === ':') {
                return $clean;
            }

            return sprintf('%s:', $clean);
        }, $schemes);
    }

    /**
     * Build directive nonce's.
     * @param array<string, mixed> $nonces
     * @return array<string>
     */
    public function nonces(array $nonces): array
    {
        return array_map(function (string $nonce) {
            $clean = trim($nonce);

            if (base64_decode($clean, true) === false) {
                return '';
            }

            return sprintf('\'nonce-%s\'', $clean);
        }, $nonces);
    }

    /**
     * Build directive hashes.
     * @param array<string, mixed> $groups
     * @return array<string>
     */
    public function hashes(array $groups): array
    {
        $result = [];

        foreach ($groups as $hash => $items) {
            if (in_array($hash, ['sha256', 'sha384', 'sha512'], true) === false) {
                continue;
            }

            foreach ($items as $item) {
                $clean = trim($item);

                if (base64_decode($clean, true) === false) {
                    continue;
                }

                $result[] = sprintf('\'%s-%s\'', $hash, $clean);
            }
        }

        return $result;
    }

    /**
     * Build plugin-types directive.
     * @return string
     */
    public function pluginTypes(): string
    {
        $pluginTypes = $this->configurations['plugin-types'] ?? [];

        $filtered = array_filter($pluginTypes, function (string $mime) {
            return preg_match('/^[a-z\-]+\/[a-z\-]+$/i', $mime);
        });

        if (count($filtered) > 0) {
            array_unshift($filtered, 'plugin-types');
        }

        return $this->implode($filtered);
    }

    /**
     * Build sandbox directive.
     * @return string
     */
    public function sandbox(): string
    {
        $sandbox = $this->configurations['sandbox'] ?? [];

        if (($sandbox['enable'] ?? false) === false) {
            return '';
        }

        $whitelist = [
            'allow-downloads-without-user-activation' => true,
            'allow-forms' => true,
            'allow-modals' => true,
            'allow-orientation-lock' => true,
            'allow-pointer-lock' => true,
            'allow-popups' => true,
            'allow-popups-to-escape-sandbox' => true,
            'allow-presentation' => true,
            'allow-same-origin' => true,
            'allow-scripts' => true,
            'allow-storage-access-by-user-activation' => true,
            'allow-top-navigation' => true,
            'allow-top-navigation-by-user-activation' => true,
        ];

        $filtered = $this->filter($sandbox, $whitelist);

        array_unshift($filtered, 'sandbox');

        return $this->implode($filtered);
    }

    /**
     * Build require-trusted-types-for directive.
     * @return string
     */
    public function requireTrustedTypesFor(): string
    {
        $config = $this->configurations['require-trusted-types-for'] ?? [];

        if (($config['script'] ?? false) === false) {
            return '';
        }

        return "require-trusted-types-for 'script'";
    }

    /**
     * Build trusted-types directive.
     * @return string
     */
    public function trustedTypes(): string
    {
        $trustedTypes = $this->configurations['trusted-types'] ?? [];

        if (($trustedTypes['enable'] ?? false) === false) {
            return '';
        }

        $policies = array_map('trim', $trustedTypes['policies'] ?? []);

        if ($trustedTypes['default'] ?? false) {
            $policies[] = 'default';
        }

        if ($trustedTypes['allow-duplicates'] ?? false) {
            $policies[] = '\'allow-duplicates\'';
        }

        array_unshift($policies, 'trusted-types');

        return $this->implode($policies);
    }

    /**
     * Build block-all-mixed-content directive.
     * @return string
     */
    public function blockAllMixedContent(): string
    {
        if (($this->configurations['block-all-mixed-content'] ?? false) === false) {
            return '';
        }

        return 'block-all-mixed-content';
    }

    /**
     * Build upgrade-insecure-requests directive.
     * @return string
     */
    public function upgradeInsecureRequests(): string
    {
        if (($this->configurations['upgrade-insecure-requests'] ?? false) === false) {
            return '';
        }

        return 'upgrade-insecure-requests';
    }

    /**
     * Build report-to directive.
     * @return string
     */
    public function reportTo(): string
    {
        if (empty($this->configurations['report-to'])) {
            return '';
        }

        return sprintf('report-to %s', $this->configurations['report-to']);
    }

    /**
     * Build report-uri directive.
     * @return string
     */
    public function reportUri(): string
    {
        if (empty($this->configurations['report-uri'])) {
            return '';
        }

        $uri = $this->implode($this->configurations['report-uri']);

        return sprintf('report-uri %s', $uri);
    }

    /**
     * Using key to filter configuration and return keys.
     * @param array<string, mixed> $config
     * @param array<string, mixed> $available
     * @return array<string>
     */
    public function filter(array $config, array $available): array
    {
        $targets = array_intersect_key($config, $available);

        $needs = array_filter($targets);

        return array_keys($needs);
    }

    /**
     * Implode strings using glue
     * @param array<mixed> $payload
     * @param string $glue
     * @return string
     */
    public function implode(array $payload, string $glue = ' '): string
    {
        return implode($glue, $payload);
    }

    /**
     * Build the directives
     * @return string
     */
    protected function directives(): string
    {
        $result = [];
        foreach ($this->configurations as $name => $config) {
            if (($this->whitelist[$name] ?? false) === false) {
                continue;
            }

            $value = $this->directive($config);

            if (empty($value)) {
                continue;
            }

            $result[] = sprintf('%s %s', $name, $value);
        }

        return $this->implode($result, '; ');
    }
}

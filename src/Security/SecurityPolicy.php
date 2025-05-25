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
 *  @file SecurityPolicy.php
 *
 *  The Security Policy class
 *
 *  @package    Platine\Framework\Security
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security;

use Platine\Config\Config;
use Platine\Framework\Security\Policy\ClearSiteDataPolicy;
use Platine\Framework\Security\Policy\ContentSecurityPolicy;
use Platine\Framework\Security\Policy\FeaturePermissionPolicy;
use Platine\Framework\Security\Policy\StrictTransportSecurityPolicy;
use Platine\Route\Router;
use Platine\Stdlib\Helper\Json;

/**
 * @class SecurityPolicy
 * @package Platine\Framework\Security
 * @template T
 */
class SecurityPolicy
{
 
    /**
     * The nonce's for script-src and style-src
     * @var array<string, array<string>>
     */
    protected array $nonces = [
        'style' => [],
        'script' => [],
    ];

    /**
     * Create new instance
     * @param Config<T> $config
     * @param Router $router
     * @param array<string, mixed> $configurations
     */
    public function __construct(
        protected Config $config,
        protected Router $router,
        protected array $configurations = []
    ) {
        
    }

    /**
     * Return the headers to be used in response
     * @return array<string, string>
     */
    public function headers(): array
    {
        $headers = array_merge(
            $this->csp(),
            $this->features(),
            $this->hsts(),
            $this->clearSiteData(),
            $this->commons()
        );

        return array_filter($headers);
    }

    /**
     * Generate random nonce value for current request.
     * @param string $target
     * @return string
     */
    public function nonce(string $target = 'script'): string
    {
        $nonce = base64_encode(bin2hex(random_bytes(8)));
        $this->nonces[$target][] = $nonce;

        return $nonce;
    }

    /**
     * Return the Content Security Policy headers
     * @return array<string, string>
     */
    protected function csp(): array
    {
        $config = $this->configurations['csp'] ?? [];
        $isEnabled = $config['enable'] ?? false;
        if ($isEnabled === false) {
            return [];
        }

        $config['script-src']['nonces'] = $this->nonces['script'];
        $config['style-src']['nonces'] = $this->nonces['style'];

        if (count($config['report-uri'] ?? []) > 0) {
            $routes = $this->router->routes();
            $reportUri = [];
            foreach ($config['report-uri'] as $url) {
                if ($routes->has($url)) {
                    $url = $this->config->get('app.host') . $this->router->getUri($url)->getPath();
                }

                $reportUri[] = $url;
            }

            $config['report-uri'] = $reportUri;
        }

        $isReportOnly = $config['report-only'] ?? false;
        $header = $isReportOnly
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

        $policy = new ContentSecurityPolicy($config);

        $headers = [$header => $policy->headers()];

        $reportTo = [];
        if ($config['report-to'] ?? false) {
            if (count($config['report-uri'] ?? []) > 0) {
                $reportTo['group'] = $config['report-to'];
                $reportTo['max_age'] = 1800; // TODO use configuration

                $reportTo['endpoints'] = [];
                foreach ($config['report-uri'] as $url) {
                    $reportTo['endpoints'][] = [
                        'url' => $url
                    ];
                }
                $headers['Report-To'] = Json::encode($reportTo);
            }
        }

        return $headers;
    }

    /**
     * Return the Permissions Policy headers
     * @return array<string, string>
     */
    protected function features(): array
    {
        $config = $this->configurations['features-permissions'] ?? [];
        $isEnabled = $config['enable'] ?? false;
        if ($isEnabled === false) {
            return [];
        }

         $policy = new FeaturePermissionPolicy($config);

        return ['Permissions-Policy' => $policy->headers()];
    }

    /**
     * Return the HSTS Policy headers
     * @return array<string, string>
     */
    protected function hsts(): array
    {
        $config = $this->configurations['hsts'] ?? [];
        $isEnabled = $config['enable'] ?? false;
        if ($isEnabled === false) {
            return [];
        }

         $policy = new StrictTransportSecurityPolicy($config);

        return ['Strict-Transport-Security' => $policy->headers()];
    }


    /**
     * Return the Clear Site Data Policy headers
     * @return array<string, string>
     */
    protected function clearSiteData(): array
    {
        $config = $this->configurations['clear-site-data'] ?? [];
        $isEnabled = $config['enable'] ?? false;
        if ($isEnabled === false) {
            return [];
        }

         $policy = new ClearSiteDataPolicy($config);

        return ['Clear-Site-Data' => $policy->headers()];
    }

    /**
     * Return the common security policies headers
     * @return array<string, string>
     */
    protected function commons(): array
    {
        return array_filter([
            'X-Content-Type-Options' => $this->configurations['x-content-type-options'] ?? 'nosniff',
            'X-Download-Options' => $this->configurations['x-download-options'] ?? 'noopen',
            'X-Frame-Options' => $this->configurations['x-frame-options'] ?? 'sameorigin',
            'X-Permitted-Cross-Domain-Policies' => $this->configurations['x-permitted-cross-domain-policies'] ?? 'none',
            'X-Powered-By' => $this->configurations['x-powered-by'] ?? '',
            'X-XSS-Protection' => $this->configurations['x-xss-protection'] ?? '1; mode=block',
            'Referrer-Policy' => $this->configurations['referrer-policy'] ?? 'no-referrer',
            'Server' => $this->configurations['server'] ?? '',
        ]);
    }
}

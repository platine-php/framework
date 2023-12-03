<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Policy;

use Platine\Dev\PlatineTestCase;

class SecurityPolicyTestCase extends PlatineTestCase
{
    public function getPolicyConfig(): array
    {
        $config = [
            'server' => '',
            'x-content-type-options' => 'nosniff',
            'x-download-options' => 'noopen',
            'x-frame-options' => 'sameorigin',
            'x-permitted-cross-domain-policies' => 'none',
            'x-powered-by' => '',
            'x-xss-protection' => '1; mode=block',
            'referrer-policy' => 'no-referrer',
            'clear-site-data' => [
                'enable' => false,
                'all' => false,
                'cache' => true,
                'cookies' => true,
                'storage' => true,
                'execution-contexts' => true,
            ],
            'hsts' => [
                'enable' => false,
                'max-age' => 31536000,
                'include-sub-domains' => false,
                'preload' => false,
            ],
            'csp' => [
                'enable' => true,
                'report-only' => false,
                'report-to' => '',
                'report-uri' => [],
                'block-all-mixed-content' => false,
                'upgrade-insecure-requests' => false,
                'base-uri' => [],
                'child-src' => [],
                'connect-src' => [],
                'default-src' => [
                    'self' => true,
                ],
                'font-src' => [],
                'form-action' => [
                    'self' => true,
                ],
                'frame-ancestors' => [
                    'self' => true,
                ],
                'frame-src' => [],
                'img-src' => [],
                'manifest-src' => [],
                'media-src' => [],
                'navigate-to' => [
                    'unsafe-allow-redirects' => false,
                ],
                'object-src' => [],
                'plugin-types' => [],
                'prefetch-src' => [],
                'require-trusted-types-for' => [
                    'script' => false,
                ],
                'sandbox' => [
                    'enable' => false,
                    'allow-downloads-without-user-activation' => false,
                    'allow-forms' => false,
                    'allow-modals' => false,
                    'allow-orientation-lock' => false,
                    'allow-pointer-lock' => false,
                    'allow-popups' => false,
                    'allow-popups-to-escape-sandbox' => false,
                    'allow-presentation' => false,
                    'allow-same-origin' => false,
                    'allow-scripts' => false,
                    'allow-storage-access-by-user-activation' => false,
                    'allow-top-navigation' => false,
                    'allow-top-navigation-by-user-activation' => false,
                ],
                'script-src' => [
                    'none' => false,
                    'self' => false,
                    'report-sample' => false,
                    'allow' => [],
                    'schemes' => [],
                    'unsafe-inline' => false,
                    'unsafe-eval' => false,
                    'unsafe-hashes' => false,
                    'strict-dynamic' => false,
                    'hashes' => [
                        'sha256' => [],
                        'sha384' => [],
                        'sha512' => [],
                    ],
                ],
                'script-src-attr' => [],
                'script-src-elem' => [],
                'style-src' => [],
                'style-src-attr' => [],
                'style-src-elem' => [],
                'trusted-types' => [
                    'enable' => false,
                    'allow-duplicates' => false,
                    'default' => false,
                    'policies' => [],
                ],
                'worker-src' => [],
            ],
            'features-permissions' => [
                'enable' => true,
                'accelerometer' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'ambient-light-sensor' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'autoplay' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'battery' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'camera' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'cross-origin-isolated' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'display-capture' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'document-domain' => [
                    'none' => false,
                    '*' => true,
                    'self' => false,
                    'origins' => [],
                ],
                'encrypted-media' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'execution-while-not-rendered' => [
                    'none' => false,
                    '*' => true,
                    'self' => false,
                    'origins' => [],
                ],
                'execution-while-out-of-viewport' => [
                    'none' => false,
                    '*' => true,
                    'self' => false,
                    'origins' => [],
                ],
                'fullscreen' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'geolocation' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'gyroscope' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'magnetometer' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'microphone' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'midi' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'navigation-override' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'payment' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'picture-in-picture' => [
                    'none' => false,
                    '*' => true,
                    'self' => false,
                    'origins' => [],
                ],
                'publickey-credentials-get' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'screen-wake-lock' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'sync-xhr' => [
                    'none' => false,
                    '*' => true,
                    'self' => false,
                    'origins' => [],
                ],
                'usb' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'web-share' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
                'xr-spatial-tracking' => [
                    'none' => false,
                    '*' => false,
                    'self' => true,
                    'origins' => [],
                ],
            ],
        ];


        return $config;
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security;

use Platine\Config\Config;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Route\RouteCollection;
use Platine\Route\Router;

/*
 * @group core
 * @group framework
 */
class SecurityPolicyTest extends SecurityPolicyTestCase
{
    public function testConstruct(): void
    {
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $config = $this->getPolicyConfig();
        $o = new SecurityPolicy($cfg, $router, $config);

        $this->assertInstanceOf(SecurityPolicy::class, $o);
        $this->assertCount(12, $config);
    }

    public function testHeaders(): void
    {
        $config = $this->getPolicyConfig();
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $this->assertCount(8, $headers);
        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $this->assertArrayHasKey('X-Content-Type-Options', $headers);
        $this->assertArrayHasKey('X-Download-Options', $headers);
        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertArrayHasKey('X-Permitted-Cross-Domain-Policies', $headers);
        $this->assertArrayHasKey('X-XSS-Protection', $headers);
        $this->assertArrayHasKey('Referrer-Policy', $headers);
        $this->assertArrayHasKey('Content-Security-Policy', $headers);
    }

    public function testCspHeaders(): void
    {
        global $mock_base64_encode_to_sample, $mock_base64_decode_to_false;
        $mock_base64_decode_to_false = true;
        $mock_base64_encode_to_sample = true;

        $config = $this->getPolicyConfig();
        $config['csp']['font-src']['none'] = true;
        $config['csp']['report-to'] = 'my-app-report';
        $config['csp']['report-uri'] = ['http://localhost'];
        $config['csp']['block-all-mixed-content'] = true;
        $config['csp']['upgrade-insecure-requests'] = true;
        $config['csp']['trusted-types']['enable'] = true;
        $config['csp']['trusted-types']['default'] = true;
        $config['csp']['trusted-types']['allow-duplicates'] = true;
        $config['csp']['require-trusted-types-for']['script'] = true;
        $config['csp']['sandbox']['enable'] = true;
        $config['csp']['plugin-types'] = ['application/pdf'];
        $config['csp']['img-src']['schemes'] = ['https:'];
        $config['csp']['media-src']['schemes'] = ['https'];
        $config['csp']['style-src']['self'] = true;
        $config['csp']['script-src']['hashes']['dd'] = ['ZmdnZw'];
        $config['csp']['script-src']['hashes']['sha256'] = ['ZmdnZw'];
        $config['csp']['script-src']['hashes']['sha512'] = ['s'];

        $routeCollection = $this->getMockInstance(RouteCollection::class, [
            'has' => false,
        ]);
        $router = $this->getMockInstance(Router::class, [
            'routes' => $routeCollection
        ]);
        $cfg = $this->getMockInstance(Config::class);

        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $o->nonce();

        $this->assertArrayHasKey('Content-Security-Policy', $headers);

        $expected = "default-src 'self'; font-src 'none'; form-action 'self'; frame-ancestors 'self'; img-src https:; media-src https:; style-src 'self'; plugin-types application/pdf; sandbox; require-trusted-types-for 'script'; trusted-types default 'allow-duplicates'; block-all-mixed-content; upgrade-insecure-requests; report-to my-app-report; report-uri http://localhost";
        $this->assertEquals($expected, $headers['Content-Security-Policy']);
    }

    public function testCspNonceAndHashHeaders(): void
    {
        global $mock_base64_encode_to_sample, $mock_base64_decode_to_false;
        $mock_base64_encode_to_sample = true;

        $config = $this->getPolicyConfig();
        $config['csp']['script-src']['hashes']['sha256'] = ['a'];
        $config['csp']['script-src']['hashes']['sha512'] = ['ZmdnZw'];
        $config['csp']['report-uri'] = ['http://localhost'];

        $routeCollection = $this->getMockInstance(RouteCollection::class, [
            'has' => true,
        ]);
        $router = $this->getMockInstance(Router::class, [
            'routes' => $routeCollection
        ]);
        $cfg = $this->getMockInstance(Config::class);

        $o = new SecurityPolicy($cfg, $router, $config);

        $o->nonce();

        $headers1 = $o->headers();

        $this->assertArrayHasKey('Content-Security-Policy', $headers1);

        $expected1 = "default-src 'self'; form-action 'self'; frame-ancestors 'self'; script-src 'sha256-a' 'sha512-ZmdnZw' 'nonce-nonce'; report-uri ";
        $this->assertEquals($expected1, $headers1['Content-Security-Policy']);

        $mock_base64_decode_to_false = true;
        $mock_base64_encode_to_sample = false;

        $o->nonce('style');

        $headers = $o->headers();

        $this->assertArrayHasKey('Content-Security-Policy', $headers);

        $expected = "default-src 'self'; form-action 'self'; frame-ancestors 'self'; report-uri ";
        $this->assertEquals($expected, $headers['Content-Security-Policy']);
    }

    public function testCspReportHeaders(): void
    {
        $config = $this->getPolicyConfig();
        $config['csp']['report-only'] = true;

        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);

        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $this->assertArrayHasKey('Content-Security-Policy-Report-Only', $headers);
        $this->assertEquals("default-src 'self'; form-action 'self'; frame-ancestors 'self'", $headers['Content-Security-Policy-Report-Only']);
    }

    public function testCspNotEnableHeaders(): void
    {
        $config = $this->getPolicyConfig();
        $config['csp']['enable'] = false;

        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);

        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $headers);
    }

    public function testFeaturesHeaders(): void
    {
        $config = $this->getPolicyConfig();
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);

        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $expected = 'accelerometer=(self), ambient-light-sensor=(self), autoplay=(self), battery=(self), camera=(self), cross-origin-isolated=(self),'
                . ' display-capture=(self), document-domain=*, encrypted-media=(self), execution-while-not-rendered=*, '
                . 'execution-while-out-of-viewport=*, fullscreen=(self), geolocation=(self), gyroscope=(self), magnetometer=(self), microphone=(self), '
                . 'midi=(self), navigation-override=(self), payment=(self), picture-in-picture=*, publickey-credentials-get=(self), screen-wake-lock=(self),'
                . ' sync-xhr=*, usb=(self), web-share=(self), xr-spatial-tracking=(self)';
        $this->assertEquals($expected, $headers['Permissions-Policy']);
    }

    public function testFeatureNoneHeaders(): void
    {
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $config = $this->getPolicyConfig();
        $config['features-permissions']['accelerometer']['none'] = true;
        $config['features-permissions']['ambient-light-sensor']['origins'] = ['http://example.com'];

        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $expected = 'accelerometer=(), ambient-light-sensor=(self "http://example.com"), autoplay=(self), battery=(self), camera=(self), cross-origin-isolated=(self),'
                . ' display-capture=(self), document-domain=*, encrypted-media=(self), execution-while-not-rendered=*, '
                . 'execution-while-out-of-viewport=*, fullscreen=(self), geolocation=(self), gyroscope=(self), magnetometer=(self), microphone=(self), '
                . 'midi=(self), navigation-override=(self), payment=(self), picture-in-picture=*, publickey-credentials-get=(self), screen-wake-lock=(self),'
                . ' sync-xhr=*, usb=(self), web-share=(self), xr-spatial-tracking=(self)';
        $this->assertEquals($expected, $headers['Permissions-Policy']);
    }

    public function testFeaturesNotEnableHeaders(): void
    {
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $config = $this->getPolicyConfig();
        $config['features-permissions']['enable'] = false;

        $o = new SecurityPolicy($cfg, $router, $config);

        $headers = $o->headers();

        $this->assertArrayNotHasKey('Permissions-Policy', $headers);
    }

    public function testClearSiteDataAllHeaders(): void
    {
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $config = $this->getPolicyConfig();
        $config['clear-site-data']['enable'] = true;
        $config['clear-site-data']['all'] = true;

        $o = new SecurityPolicy($cfg, $router, $config);


        $headers = $o->headers();

        $this->assertArrayHasKey('Clear-Site-Data', $headers);
        $this->assertEquals('"*"', $headers['Clear-Site-Data']);
    }

    public function testClearSiteDataHeaders(): void
    {
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $config = $this->getPolicyConfig();
        $config['clear-site-data']['enable'] = true;

        $o = new SecurityPolicy($cfg, $router, $config);


        $headers = $o->headers();

        $this->assertArrayHasKey('Clear-Site-Data', $headers);
        $this->assertEquals('"cache", "cookies", "storage", "executionContexts"', $headers['Clear-Site-Data']);
    }

    public function testHstsHeaders(): void
    {
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);
        $config = $this->getPolicyConfig();
        $config['hsts']['enable'] = true;
        $config['hsts']['preload'] = true;
        $config['hsts']['include-sub-domains'] = true;

        $o = new SecurityPolicy($cfg, $router, $config);


        $headers = $o->headers();

        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertEquals('max-age=31536000; includeSubDomains; preload', $headers['Strict-Transport-Security']);
    }

    public function testGenerateNonce(): void
    {
        global $mock_base64_encode_to_sample;
        $mock_base64_encode_to_sample = true;
        $config = $this->getPolicyConfig();
        $router = $this->getMockInstance(Router::class);
        $cfg = $this->getMockInstance(Config::class);

        $o = new SecurityPolicy($cfg, $router, $config);

        $nonceScript = $o->nonce('script');
        $nonceStyle = $o->nonce('style');

        $this->assertEquals('nonce', $nonceScript);
        $this->assertEquals('nonce', $nonceStyle);
    }
}

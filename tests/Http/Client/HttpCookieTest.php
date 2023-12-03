<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Client;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Client\HttpCookie;

class HttpCookieTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $cookieString = 'language=en; '
            . 'Expires=Wed, 01-Jul-2020 00:00:00 GMT; '
            . 'Max-Age=10; Domain=example.com; Path=/secure; Secure; '
            . 'HttpOnly; SameSite=Lax';

        $o = new HttpCookie($cookieString);
        $this->assertTrue($o->isHttpOnly());
        $this->assertTrue($o->isSecure());
        $this->assertEquals($o->getDomain(), 'example.com');
        $this->assertEquals($o->getExpires(), 1593561600);
        $this->assertEquals($o->getMaxAge(), 10);
        $this->assertEquals($o->getName(), 'language');
        $this->assertEquals($o->getValue(), 'en');
        $this->assertEquals($o->getPath(), '/secure');
        $this->assertEquals($o->getSameSite(), 'Lax');
    }
}

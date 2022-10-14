<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Encoder;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\JWT\Encoder\Base64UrlSafeEncoder;

/*
 * @group core
 * @group framework
 */
class Base64UrlSafeEncoderTest extends PlatineTestCase
{
    public function testEncode(): void
    {
        global $mock_base64_encode_to_same;
        $mock_base64_encode_to_same = true;

        $o = new Base64UrlSafeEncoder();
        $this->assertEquals('foo-bar_baz', $o->encode('foo+bar/baz=='));
    }

    public function testDecode(): void
    {
        global $mock_base64_decode_to_same;
        $mock_base64_decode_to_same = true;

        $o = new Base64UrlSafeEncoder();
        $this->assertEquals('foo+bar/baz', $o->decode('foo-bar_baz'));
    }
}

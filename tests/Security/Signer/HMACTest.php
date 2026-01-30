<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Signer;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\JWT\Exception\InvalidAlgorithmException;
use Platine\Framework\Security\JWT\Signer\HMAC;

/*
 * @group core
 * @group framework
 */
class HMACTest extends PlatineTestCase
{
    public function testConstructInvalidAlgo(): void
    {
        global $mock_hash_hmac_algos_to_empty;
        $mock_hash_hmac_algos_to_empty = true;

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'foo_algo']
            ]
        ]);
        $this->expectException(InvalidAlgorithmException::class);
        $o = new HMAC($config);
    }

    public function testConstructSuccess(): void
    {
        global $mock_hash_hmac_algos_to_foo;
        $mock_hash_hmac_algos_to_foo = true;

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'foo'],
                ['api.sign.token_header_algo', '', 'foo_header'],
            ]
        ]);
        $o = new HMAC($config);
        $this->assertEquals('foo', $o->getSignatureAlgo());
        $this->assertEquals('foo_header', $o->getTokenAlgoName());
    }

    public function testSign(): void
    {
        global $mock_hash_hmac_algos_to_foo,
               $mock_hash_hmac_to_same;
        $mock_hash_hmac_algos_to_foo = true;
        $mock_hash_hmac_to_same = true;

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'foo']
            ]
        ]);
        $o = new HMAC($config);
        $this->assertEquals('foo|my_data|my_key|true', $o->sign('my_data', 'my_key'));
    }

    public function testVerify(): void
    {
        global $mock_hash_hmac_algos_to_foo,
               $mock_hash_equals_to_false,
               $mock_hash_equals_to_true,
               $mock_hash_hmac_to_same;
        $mock_hash_hmac_algos_to_foo = true;
        $mock_hash_equals_to_false = true;
        $mock_hash_hmac_to_same = true;

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'foo']
            ]
        ]);
        $o = new HMAC($config);
        $this->assertFalse($o->verify('my_data', 'signature', 'my_key'));

        $mock_hash_equals_to_false = false;
        $mock_hash_equals_to_true = true;
        $this->assertTrue($o->verify('my_data', 'signature', 'my_key'));
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\JWT\Encoder\Base64UrlSafeEncoder;
use Platine\Framework\Security\JWT\EncoderInterface;
use Platine\Framework\Security\JWT\Exception\InvalidTokenException;
use Platine\Framework\Security\JWT\Exception\JWTException;
use Platine\Framework\Security\JWT\Exception\TokenExpiredException;
use Platine\Framework\Security\JWT\JWT;
use Platine\Framework\Security\JWT\Signer\HMAC;
use Platine\Framework\Security\JWT\SignerInterface;

/*
 * @group core
 * @group framework
 */
class JWTTest extends PlatineTestCase
{
    public function testConstructSetGet(): void
    {
        $signer = $this->getMockInstance(HMAC::class);
        $encoder = $this->getMockInstance(Base64UrlSafeEncoder::class);
        $o = new JWT($signer, $encoder);

        $this->assertInstanceOf(SignerInterface::class, $o->getSigner());
        $this->assertInstanceOf(EncoderInterface::class, $o->getEncoder());
        $this->assertEquals($encoder, $o->getEncoder());
        $this->assertEquals($signer, $o->getSigner());

        $signerSet = $this->getMockInstance(HMAC::class);
        $encoderSet = $this->getMockInstance(Base64UrlSafeEncoder::class);

        $o->setEncoder($encoderSet);
        $o->setSigner($signerSet);
        $this->assertEquals($encoderSet, $o->getEncoder());
        $this->assertEquals($signerSet, $o->getSigner());
    }

    public function testSign(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
            'sign' => 'signature_text'
        ]);
        $encoder = $this->getMockInstance(Base64UrlSafeEncoder::class, [
            'encode' => 'signature_text_encode'
        ]);
        $o = new JWT($signer, $encoder);
        $o->setSecret('my_secret');
        $this->assertEquals('my_secret', $o->getSecret());

        $this->assertFalse($o->isSigned());
        $this->assertEquals('signature_text', $o->sign());
        $this->assertEquals('signature_text', $o->getSignature());
        $this->assertEquals(
            'signature_text_encode.signature_text_encode.signature_text_encode',
            $o->getToken()
        );
        $this->assertTrue($o->isSigned());
    }

    public function testHeadersAndPayload(): void
    {
        $signer = $this->getMockInstance(HMAC::class);
        $encoder = $this->getMockInstance(Base64UrlSafeEncoder::class);
        $o = new JWT($signer, $encoder);
        $o->setHeaders([
            'typ' => 'JWS'
        ]);
        $o->setPayload([
            'id' => 1
        ]);

        $headers = $o->getHeaders();
        $payload = $o->getPayload();

        $this->assertCount(1, $headers);
        $this->assertCount(1, $payload);

        $this->assertArrayHasKey('typ', $headers);
        $this->assertArrayHasKey('id', $payload);
        $this->assertEquals('JWS', $headers['typ']);
        $this->assertEquals(1, $payload['id']);
    }

    public function testGetSignatureNotYetSigned(): void
    {
        $signer = $this->getMockInstance(HMAC::class);
        $encoder = $this->getMockInstance(Base64UrlSafeEncoder::class);
        $o = new JWT($signer, $encoder);

        $this->expectException(JWTException::class);
        $o->getSignature();
    }

    public function testDecodeInvalidTokenFormat(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
        ]);
        $encoder = $this->getMockInstance(Base64UrlSafeEncoder::class, [
        ]);
        $o = new JWT($signer, $encoder);

        $this->expectException(InvalidTokenException::class);
        $o->decode('token');
    }

    public function testDecodeInvalidHeaderAlgo(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
        ]);
        $encoder = $this->getMockInstanceMap(Base64UrlSafeEncoder::class, [
            'decode' => [
                ['headers', '{}'],
                ['payloads', '{}'],
            ]
        ]);
        $o = new JWT($signer, $encoder);

        $this->expectException(InvalidTokenException::class);
        $o->decode('headers.payloads.signature');
    }

    public function testDecodeTokenIsInvalid(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
            'getTokenAlgoName' => 'HS256'
        ]);
        $encoder = $this->getMockInstanceMap(Base64UrlSafeEncoder::class, [
            'decode' => [
                ['headers', '{"alg":"HS256"}'],
                ['payloads', '{"id":34}'],
                ['signature', 'fakesignature'],
            ]
        ]);
        $o = new JWT($signer, $encoder);

        $this->expectException(InvalidTokenException::class);
        $o->decode('headers.payloads.signature');

        $this->assertEquals('headers.payloads.signature', $o->getOriginalToken());
        $this->assertEquals('signature', $o->getEncodedSignature());
    }

    public function testDecodeTokenIsExpired(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
            'getTokenAlgoName' => 'HS256',
            'verify' => true,
        ]);
        $encoder = $this->getMockInstanceMap(Base64UrlSafeEncoder::class, [
            'decode' => [
                ['headers', '{"alg":"HS256"}'],
                ['payloads', '{"id":34,"exp":100}'],
                ['signature', 'signature'],
            ]
        ]);

        $o = new JWT($signer, $encoder);
        $o->setSecret('my_secret');

        $this->expectException(TokenExpiredException::class);
        $o->decode('headers.payloads.signature');
    }

    public function testDecodeTokenSuccess(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
            'getTokenAlgoName' => 'HS256',
            'verify' => true,
        ]);
        $encoder = $this->getMockInstanceMap(Base64UrlSafeEncoder::class, [
            'decode' => [
                ['headers', '{"alg":"HS256"}'],
                ['payloads', '{"id":34,"exp":' . (time() + 10000) . '}'],
                ['signature', 'signature'],
            ]
        ]);

        $o = new JWT($signer, $encoder);
        $o->setSecret('my_secret');

        $o->decode('headers.payloads.signature');
        $this->assertTrue($o->isValid());
        $this->assertFalse($o->isExpired());
        $this->assertEquals('headers.payloads.signature', $o->getOriginalToken());
    }

    public function testGetTokenSignatureNotYetGenerate(): void
    {
        $signer = $this->getMockInstance(HMAC::class, [
            'getTokenAlgoName' => 'HS256',
            'verify' => true,
        ]);
        $encoder = $this->getMockInstance(Base64UrlSafeEncoder::class, [
            'encode' => 'encode_value'
        ]);

        $o = new JWT($signer, $encoder);
        $o->setOriginalToken('token');
        $this->assertEquals('encode_value.encode_value', $o->getTokenSignature());
    }

    public function testIsExpiredPayloadNotFound(): void
    {
        $signer = $this->getMockInstance(HMAC::class);
        $encoder = $this->getMockInstanceMap(Base64UrlSafeEncoder::class);

        $o = new JWT($signer, $encoder);
        $this->assertFalse($o->isExpired());
    }

    public function testIsExpiredTimeIsNumeric(): void
    {
        $signer = $this->getMockInstance(HMAC::class);
        $encoder = $this->getMockInstanceMap(Base64UrlSafeEncoder::class);

        $o = new JWT($signer, $encoder);
        $o->setPayload([
            'exp' => '100'
        ]);
        $this->assertTrue($o->isExpired());
    }
}

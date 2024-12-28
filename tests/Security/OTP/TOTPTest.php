<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\OTP;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Security\OTP\TOTP;

/*
 * @group core
 * @group framework
 */
class TOTPTest extends PlatineTestCase
{
    public function testConstructSecretIsNull(): void
    {
        global $mock_random_int;
        $mock_random_int = true;

        $o = new TOTP(null);
        $this->assertEquals('BBBBBBBBBBBBBBBB', $o->getSecret());
    }

    public function testConstructSecretIsSet(): void
    {
        $o = new TOTP('FOOBAR');
        $this->assertEquals('FOOBAR', $o->getSecret());
    }

    public function testGetCode(): void
    {
        global $mock_str_pad_to_value;
        $mock_str_pad_to_value = '147570';

        $o = new TOTP();
        $code = $o->getCode(null);
        $this->assertEquals('147570', $code);
    }

    public function testGetCodeCustomSecret(): void
    {
        global $mock_str_pad_to_value;
        $mock_str_pad_to_value = '326390';

        $o = new TOTP();
        $code = $o->getCode('JX4JXO3HWYRZOKSU');
        $this->assertEquals('326390', $code);
    }

    public function testVerify(): void
    {
        global $mock_str_pad_to_value;
        $mock_str_pad_to_value = '147570';

        $o = new TOTP();
        $this->assertTrue($o->verify('147570', null));
        $this->assertFalse($o->verify('247570', null));
    }

    public function testGetURL(): void
    {
        global $mock_random_int;
        $mock_random_int = true;

        $o = new TOTP();
        $url = $o->getURL('Tony', 'Platine App');
        $this->assertEquals('otpauth://totp/Tony?secret=BBBBBBBBBBBBBBBB&issuer=Platine%20App', $url);
    }


    public function testGetSet(): void
    {
        $o = new TOTP(null);
        $o->setDigit(10);
        $o->setSecret('FOOBAR');
        $o->setSecretLength(16);
        $o->setTimeStep(60);

        $this->assertEquals('FOOBAR', $o->getSecret());
        $this->assertEquals(10, $o->getDigit());
        $this->assertEquals(16, $o->getSecretLength());
        $this->assertEquals(60, $o->getTimeStep());
    }
}

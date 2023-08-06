<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Client;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Client\HttpStatus;

class HttpStatusTest extends PlatineTestCase
{
    public function testIsXXX(): void
    {
        $this->assertTrue(HttpStatus::isInformational(100));
        $this->assertFalse(HttpStatus::isInformational(200));
        $this->assertTrue(HttpStatus::isSuccessful(200));
        $this->assertFalse(HttpStatus::isSuccessful(300));
        $this->assertTrue(HttpStatus::isRedirection(300));
        $this->assertFalse(HttpStatus::isRedirection(400));
        $this->assertTrue(HttpStatus::isClientError(400));
        $this->assertFalse(HttpStatus::isClientError(500));
        $this->assertTrue(HttpStatus::isServerError(500));
        $this->assertFalse(HttpStatus::isServerError(600));
        $this->assertTrue(HttpStatus::isError(400));
        $this->assertTrue(HttpStatus::isError(500));
        $this->assertFalse(HttpStatus::isError(600));
    }

    public function testGetReasonPhrase(): void
    {
        $this->assertEquals(HttpStatus::getReasonPhrase(200), 'OK');
        $this->assertEquals(HttpStatus::getReasonPhrase(100), 'Continue');

        // Invalid code
        $this->expectException(InvalidArgumentException::class);
        HttpStatus::getReasonPhrase(700);
    }
}

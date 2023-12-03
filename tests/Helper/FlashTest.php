<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Flash;
use Platine\Session\Session;

class FlashTest extends PlatineTestCase
{
    /**
     * @dataProvider flashDataProvider
     * @param string $key
     * @param string $value
     * @return void
     */
    public function testAll(string $key, string $value): void
    {
        $suffix = ucfirst($key);
        $setMethod = 'set' . $suffix;
        $getMethod = 'get' . $suffix;
        $session = $this->getMockInstanceMap(Session::class, [
            'getFlash' => [
                [$key, null, $value]
            ],
        ]);
        $o = new Flash($session);
        $o->{$setMethod}($value);

        $this->assertEquals($value, $o->{$getMethod}());
    }

    /**
     * Data provider for "testAll"
     * @return array
     */
    public function flashDataProvider(): array
    {
        return [
            ['success', 'success message'],
            ['error', 'error message'],
            ['info', 'info message'],
            ['warning', 'warning message'],
        ];
    }
}

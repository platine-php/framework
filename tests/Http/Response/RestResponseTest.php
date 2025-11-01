<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Response;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Response\RestResponse;

/*
 * @group core
 * @group framework
 */
class RestResponseTest extends PlatineTestCase
{
    public function testAll(): void
    {
        global $mock_time_to_1000;

        $mock_time_to_1000 = true;

        $o = new RestResponse(
            ['foo' => 'bar'],
            ['message' => 'my rest message']
        );
        $this->assertEquals(200, $o->getStatusCode());
        $this->assertEquals('application/json', $o->getHeaderLine('Content-Type'));
        $expected = '{"success":true,"timestamp":1000,"code":"OK","data":{"foo":"bar"},'
                . '"message":"my rest message"}';
        $this->assertEquals($expected, (string) $o->getBody());
    }
}

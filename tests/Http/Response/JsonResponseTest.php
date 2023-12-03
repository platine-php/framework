<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Response;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Response\JsonResponse;

/*
 * @group core
 * @group framework
 */
class JsonResponseTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $o = new JsonResponse(['foo' => 'bar']);
        $this->assertEquals(200, $o->getStatusCode());
        $this->assertEquals('application/json', $o->getHeaderLine('Content-Type'));
        $this->assertEquals('{"foo":"bar"}', (string) $o->getBody());
    }
}

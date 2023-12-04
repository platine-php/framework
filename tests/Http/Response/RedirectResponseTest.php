<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Response;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Response\RedirectResponse;

/*
 * @group core
 * @group framework
 */
class RedirectResponseTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $o = new RedirectResponse('foo/bar');
        $this->assertEquals(302, $o->getStatusCode());
        $this->assertEquals('foo/bar', $o->getHeaderLine('Location'));
    }
}

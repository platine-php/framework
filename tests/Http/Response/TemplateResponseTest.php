<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Response;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Template\Template;

/*
 * @group core
 * @group framework
 */
class TemplateResponseTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $template = $this->getMockInstance(Template::class, [
            'render' => 'template response'
        ]);
        $o = new TemplateResponse($template, 'home');
        $this->assertInstanceOf(Template::class, $o->getTemplate());
        $this->assertEquals($template, $o->getTemplate());
        $this->assertEquals(200, $o->getStatusCode());
        $this->assertEquals('template response', (string) $o->getBody());
    }
}

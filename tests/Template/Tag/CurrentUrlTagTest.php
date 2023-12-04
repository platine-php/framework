<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\CurrentUrlTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class CurrentUrlTagTest extends PlatineTestCase
{
    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_server_request_methods;

        $mock_app_to_instance = true;

        $mock_app_server_request_methods = [
            'getUri' => 'http://localhost'
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $o = new CurrentUrlTag('myname', $tokens, $parser);

        $this->assertEquals('http://localhost/', $o->render($context));
    }
}

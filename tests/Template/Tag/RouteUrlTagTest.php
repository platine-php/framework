<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\RouteUrlTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class RouteUrlTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $this->expectException(ParseException::class);
        $o = new RouteUrlTag('', $tokens, $parser);
    }




    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_route_helper_methods;

        $mock_app_to_instance = true;

        $mock_app_route_helper_methods = [
            'generateUrl' => 'http://localhost'
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class, [
            'hasKey' => true,
            'get' => 'context_var',
        ]);

        $tokens = [];
        $o = new RouteUrlTag('users id:1', $tokens, $parser);

        $this->assertEquals('http://localhost', $o->render($context));
    }
}

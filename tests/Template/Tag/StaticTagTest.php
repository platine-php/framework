<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\StaticTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class StaticTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $this->expectException(ParseException::class);
        $o = new StaticTag('', $tokens, $parser);
    }




    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_config_items;

        $mock_app_to_instance = true;

        $mock_app_config_items = [
            'app.url' => 'http://localhost/myapp',
            'app.static_dir' => 'static',
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $o = new StaticTag('css/style.css', $tokens, $parser);

        $this->assertEquals('http://localhost/myapp/static/css/style.css', $o->render($context));
    }
}

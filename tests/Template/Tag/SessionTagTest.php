<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\SessionTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class SessionTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $this->expectException(ParseException::class);
        $o = new SessionTag('', $tokens, $parser);
    }




    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_session_items;

        $mock_app_to_instance = true;

        $mock_app_session_items = [
            'user_id' => 100
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $o = new SessionTag('user_id', $tokens, $parser);

        $this->assertEquals(100, $o->render($context));
    }
}

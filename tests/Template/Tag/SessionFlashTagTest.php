<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\SessionFlashTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class SessionFlashTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $this->expectException(ParseException::class);
        $o = new SessionFlashTag('', $tokens, $parser);
    }




    public function testRender(): void
    {
        global $mock_app_to_instance,
               $mock_app_session_flash;

        $mock_app_to_instance = true;

        $mock_app_session_flash = [
            'flash_success' => 'flash message'
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $o = new SessionFlashTag('flash_success', $tokens, $parser);

        $this->assertEquals('flash message', $o->render($context));
    }
}

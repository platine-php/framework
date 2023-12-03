<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\LangTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class LangTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $this->expectException(ParseException::class);
        $o = new LangTag('', $tokens, $parser);
    }


    public function testRenderString(): void
    {
        global $mock_app_to_instance,
               $mock_app_lang_methods;

        $mock_app_to_instance = true;

        $mock_app_lang_methods = [
            'tr' => 'translate_string'
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class);

        $tokens = [];
        $o = new LangTag('user.name', $tokens, $parser);

        $this->assertEquals('translate_string', $o->render($context));
    }

    public function testRenderContext(): void
    {
        global $mock_app_to_instance,
               $mock_app_lang_methods;

        $mock_app_to_instance = true;

        $mock_app_lang_methods = [
            'tr' => 'translate_context'
        ];

        $parser = $this->getMockInstance(Parser::class);
        $context = $this->getMockInstance(Context::class, [
            'hasKey' => true,
            'get' => 'context_var',
        ]);

        $tokens = [];
        $o = new LangTag('user.name', $tokens, $parser);

        $this->assertEquals('translate_context', $o->render($context));
    }
}

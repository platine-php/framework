<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\SelectTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class SelectTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $this->expectException(ParseException::class);
        $b = new SelectTag('', $tokens, $parser);
    }

    public function testRenderNoAttributeFound(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new SelectTag('select', $tokens, $parser);

        $c = new Context();
        $res = $b->render($c);
        $this->assertEmpty($res);
    }

    public function testRenderCommon(): void
    {
        global $mock_app_lang_methods,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_lang_methods = [
            'tr' => 'Foo lang return',
        ];

        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new SelectTag('name:foo key:id display:name width:2 label:Firstname value:1 data:data', $tokens, $parser);

        $c = new Context([
            'data' => [['id' => 1, 'name' => 'Foo'],['id' => 2, 'name' => 'Bar']],
            'errors' => ['foo' => 'input failed validation']]);
        $res = $b->render($c);
        $expected = '<div class="form-group row"><label for="foo" class="col-md-10 col-form-label">'
                . 'Firstname: </label><div class="col-md-2"><select class="form-control form-control-sm '
                . 'select2js" name="foo" placeholder="Foo lang return" id="foo">'
                . '<option value="">-- Foo lang return --</option><option value="1" selected>Foo</option>'
                . '<option value="2">Bar</option></select>'
                . '<span class="ferror">input failed validation</span></div></div>';

        $this->assertEquals($expected, $res);
    }

    public function testRenderDataNotArray(): void
    {
        global $mock_app_lang_methods,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_lang_methods = [
            'tr' => 'None lang',
        ];

        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new SelectTag('name:foo key:id display:name width:2 label:Firstname value:1 data:1', $tokens, $parser);

        $c = new Context([
            'data' => [['id' => 1, 'name' => 'Foo'],['id' => 2, 'name' => 'Bar']],
            'errors' => ['foo' => 'input failed validation']]);
        $res = $b->render($c);
        $expected = '<div class="form-group row"><label for="foo" class="col-md-10 col-form-label">'
                . 'Firstname: </label><div class="col-md-2"><select class="form-control form-control-sm '
                . 'select2js" name="foo" placeholder="None lang" id="foo">'
                . '<option value="">-- None lang --</option></select>'
                . '<span class="ferror">input failed validation</span></div></div>';

        $this->assertEquals($expected, $res);
    }
}

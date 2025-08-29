<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Template\Tag\FieldTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Test\Framework\Fixture\MyParam;

/*
 * @group core
 * @group framework
 */
class FieldTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $this->expectException(ParseException::class);
        $b = new FieldTag('', $tokens, $parser);
    }

    public function testRenderNoAttributeFound(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new FieldTag('field', $tokens, $parser);

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
        $b = new FieldTag(
            'type:textarea name:foo foo:var_value width:2 label:Firstname required:true',
            $tokens,
            $parser
        );

        $c = new Context(['var_value' => 345, 'errors' => ['foo' => 'input failed validation']]);
        $res = $b->render($c);
        $expected = '<div class="form-group row mb-2"><label for="foo" class="col-md-10 '
                . 'col-form-label">Firstname: <span class="required">*</span></label>'
                . '<div class="col-md-2"><textarea class="form-control form-control-sm" '
                . 'required="1" name="foo" foo="345" placeholder="Foo lang return" id="foo">'
                . '</textarea><span class="ferror">input failed validation</span></div></div>';

        $this->assertEquals($expected, $res);
    }

    public function testRenderCommonValueFromBaseParam(): void
    {
        global $mock_app_lang_methods,
           $mock_app_form_server_request_methods,
           $mock_app_form_to_instance,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_form_to_instance = true;
        $mock_app_lang_methods = [
            'tr' => 'Foo lang return',
        ];
        $mock_app_form_server_request_methods = ['getParsedBody' => ['foo' => 'tnh']];

        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new FieldTag('name:foo', $tokens, $parser);

        $param = new MyParam();
        $c = new Context(['param' => $param]);
        $res = $b->render($c);
        $expected = '<div class="form-group row"><label for="foo" class="col-md-3 '
                . 'col-form-label"> </label><div class="col-md-9"><input type="text" '
                . 'class="form-control form-control-sm" name="foo" placeholder="Foo lang return" '
                . 'id="foo"></div></div>';
        $this->assertEquals($expected, $res);
    }
}

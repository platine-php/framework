<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Filter;
use Platine\Lang\Lang;

class FilterTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);

        $this->assertInstanceOf(Filter::class, $o);
        $this->assertCount(0, $o->getAttributes());
        $o->setAttribute('foo', 'bar');

        $this->assertCount(1, $o->getAttributes());
        $this->assertArrayHasKey('foo', $o->getAttributes());

        $o->setAttributes(['bar' => 'foo']);
        $this->assertCount(1, $o->getAttributes());
        $this->assertArrayHasKey('bar', $o->getAttributes());

        $o->setSearchFilterKeepFields(['name']);
        $o->setParams(['name' => 1, 'search' => 'foo', 'status' => 'Y']);
        $this->assertCount(2, $o->getParams());
        $this->assertEquals(1, $o->getParam('name'));
        $this->assertEquals('foo', $o->getParam('search'));

        $o->setKeepOnlySearchFilter(false);
        $this->assertCount(3, $o->getParams());
        $this->assertEquals(1, $o->getParam('name'));
        $this->assertEquals('Y', $o->getParam('status'));

        $o->setParams([]);
        $this->assertCount(0, $o->getParams());
        $o->setKeepOnlySearchFilter(true);
        $o->setParams(['name' => 1]);
        $this->assertCount(1, $o->getParams());
    }

    public function testRenderCommonText(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);
        $this->assertEmpty($o->render());

        $o->setAttribute('foo', 'bar');
        $o->addDateField('date', 'Start Date', '2025-08-23');
        $o->addTextField('search', '');
        $o->addHiddenField('status', '', 'Y', ['required' => false, 'new_line' => true]);
        $this->assertCount(3, $o->getFields());
        $res = $o->render();
        $expected = $this->getEnclosedContent(
            '<label for="date">Start Date:</label> &nbsp;&nbsp;'
            . '<input name="date" type="date" id="date" value="2025-08-23" /> &nbsp;&nbsp;'
            . '<input name="search" type="text" id="search" /> &nbsp;&nbsp;'
            . '<input name="status" type="hidden" id="status" value="Y" /><br /><br />',
            'foo="bar"'
        );

        $this->assertEquals($expected, $res);
    }

    public function testRenderSelect(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);

        $row = new \stdClass();
        $row->id = 1;
        $row->name = 'Foo';

        $o->addSelectField(
            'status',
            'Status',
            [$row],
            '',
            ['required' => false, 'new_line' => true, 'format_function' => fn($o) => $o->name]
        );
        $res = $o->render();
        $expected = $this->getEnclosedContent(
            '<label for="status">Status:</label> &nbsp;&nbsp;<select name="status" '
                . 'id="status"><option value="">--  --</option><option value="1" >Foo</option>'
                . '</select><br /><br />',
            ''
        );

        $this->assertEquals($expected, $res);
    }

    public function testRenderSelectIndexedArray(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);

        $row = new \stdClass();
        $row->id = 1;
        $row->name = 'Foo';

        $o->addSelectField(
            'status',
            'Status',
            ['name'],
            '',
            ['required' => false]
        );
        $res = $o->render();
        $expected = $this->getEnclosedContent(
            '<label for="status">Status:</label> '
            . '&nbsp;&nbsp;<select name="status" id="status"><option value="">'
                . '--  --</option><option value="name" >name</option></select>',
            ''
        );

        $this->assertEquals($expected, $res);
    }

    public function testRenderSelectEmtptyData(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);

        $row = new \stdClass();
        $row->id = 1;
        $row->name = 'Foo';

        $o->addSelectField(
            'status',
            'Status',
            [],
            '',
            ['required' => false]
        );
        $res = $o->render();
        $expected = $this->getEnclosedContent(
            '',
            ''
        );

        $this->assertEquals($expected, $res);
    }

    public function testRenderSelectNotFormatFunction(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);

        $row = new \stdClass();
        $row->id = 1;
        $row->name = 'Foo';

        $o->addSelectField(
            'status',
            'Status',
            [$row],
            '',
            ['required' => false]
        );
        $res = $o->render();
        $expected = $this->getEnclosedContent(
            '<label for="status">Status:</label> &nbsp;&nbsp;<select name="status"'
                . ' id="status"><option value="">--  --'
                . '</option><option value="1" >stdClass</option></select>',
            ''
        );

        $this->assertEquals($expected, $res);
    }

    public function testRenderForm(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $o = new Filter($lang);
        $this->assertEmpty($o->form());

        $o->setAttribute('foo', 'bar');
        $o->addDateField('date', 'Start Date', '2025-08-23');
        $o->addTextField('search', '');
        $o->addHiddenField(
            'hidden',
            '',
            'Y',
            [
            'class' => 'my-class',
            'required' => false,
            'new_line' => true]
        );
        $row = new \stdClass();
        $row->id = 1;
        $row->name = 'Foo';

        $o->addSelectField(
            'status',
            'Status',
            [$row],
            '',
            ['required' => false]
        );

        $o->addSelectField(
            'foo',
            '',
            ['name_foo'],
            '',
            ['required' => false, 'class' => 'css-class']
        );

        $res = $o->form();
        $expected = '<div class="form-group row"><label for="date" class="col-md-3 '
                . 'col-form-label">Start Date:</label><div class="col-md-9">'
                . '<input name="date" type="date" id="date" class="form-control '
                . 'form-control-sm" value="2025-08-23" /></div></div>'
                . '<div class="form-group row"><label for="search" class="col-md-3 '
                . 'col-form-label">&nbsp;</label><div class="col-md-9">'
                . '<input name="search" type="text" id="search" '
                . 'class="form-control form-control-sm" /></div></div>'
                . '<div class="form-group row"><label for="hidden" '
                . 'class="col-md-3 col-form-label">&nbsp;</label>'
                . '<div class="col-md-9"><input class="my-class form-control '
                . 'form-control-sm" name="hidden" type="hidden" id="hidden" '
                . 'value="Y" /><br /><br /></div></div><div class="form-group row">'
                . '<label for="status" class="col-md-3 col-form-label">'
                . 'Status:</label><div class="col-md-9"><select name="status" '
                . 'id="status" class="form-control form-control-sm">'
                . '<option value="">--  --</option><option value="1" >stdClass</option>'
                . '</select></div></div><div class="form-group row">'
                . '<label for="foo" class="col-md-3 col-form-label">&nbsp;</label>'
                . '<div class="col-md-9"><select class="css-class form-control '
                . 'form-control-sm" name="foo" id="foo"><option value="">'
                . '--  --</option><option value="name_foo" >name_foo</option>'
                . '</select></div></div>';

        $this->assertEquals($expected, $res);
    }

    private function getEnclosedContent(string $str, string $attr = ''): string
    {
        return sprintf('<div class="text-right">'
            . '<div><button data-bs-toggle="collapse" data-bs-target=".app-form-filter" '
                . 'class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> '
                . '</button></div><br />'
                . '<form action="" method="GET" %s data-form-type="filter" '
                . 'class="collapse app-form-filter">%s &nbsp;&nbsp;'
                . '<button type="submit" class="btn btn-xs btn-primary"'
                . '></button></form><br /></div>', $attr, $str);
    }
}

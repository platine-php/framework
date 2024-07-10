<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\InputClean;

/*
 * @group core
 * @group framework
 */
class InputCleanTest extends PlatineTestCase
{
    public function testDefault(): void
    {

        $o = new InputClean();
        $this->assertEquals('UTF-8', $o->getCharset());
        $o->setCharset('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $o->getCharset());
        $this->assertEquals('pathxss', $o->sanitizeFilename('./path/xss'));
        $this->assertEquals('tnh.png', $o->stripImageTags('<img src="tnh.png">'));
        $this->assertTrue($o->clean('img', true));
        $this->assertFalse($o->clean('<img src="tmg.png" />', true));
    }

    /**
     * @dataProvider commonDataProvider
     * @param mixed $input
     * @param mixed $expected
     */
    public function testAll($input, $expected)
    {
        $o = new InputClean();
        $res = $o->clean($input);

        $this->assertEquals($res, $expected);
    }

    /**
     * Data provider for "testAll"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
            [null, null],
            ['', ''],
            ['DOE John', 'DOE John'],
            ['éer', 'éer'],
            ['45', '45'],
            [true, true],
            [false, false],
            [4, 4],
            [
                '<a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>',
                '<a >Google</a >'
            ],
            [
                '<a data-bin="foo" href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">',
                '<a >'
            ],
            [
                '2024-07-01',
                '2024-07-01'
            ],
            [
                'a=5&b=1',
                'a=5&b=1'
            ],
            [
                '&nbsp;',
                '&nbsp;'
            ],
            [
                'alert("hello");',
                'alert&#40;"hello"&#41;;'
            ],
            [
                'https://example.com/index.php?a=1&c=%3c',
                'https://example.com/index.php?a=1&c=<'
            ],
            [
                "java\tscript",
                'java script'
            ],
            [
                'javascript:call();',
                '[removed]call();'
            ],
            [
                '<img src="tnh.png">',
                '<img >'
            ],
            [
                '<script src="tnh.js">',
                '[removed]'
            ],
            [
                '<blink>',
                '&lt;blink&gt;'
            ],
            [
                '<?php eval("foo");',
                '&lt;?php eval&#40;"foo"&#41;;'
            ],
        ];
    }
}

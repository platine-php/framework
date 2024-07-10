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
            ['<img src=x onerror=alert(123) />', '<img  />'],
            [
                '<svg><script>123<1>alert(123)</script>',
                '&lt;svg&gt;[removed]123<1>alert&#40;123&#41;[removed]'
            ],
            ['-9223372036854775808/-1', '-9223372036854775808/-1'],
            [true, true],
            [false, false],
            [4, 4],
            ["\"><script>alert(123)</script>", '">[removed]alert&#40;123&#41;[removed]'],
            [
                '<a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>',
                '<a>Google</a>'
            ],
            [
                '<a data-bin="foo" href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">',
                '<a>'
            ],
            [
                "<foo val=`bar' />",
                '<foo>'
            ],
            [
                "&lt;script&gt;alert(&#39;123&#39;);&lt;/script&gt;",
                '&lt;script&gt;alert&#40;&#39;123&#39;&#41;;&lt;/script&gt;'
            ],
            [
                "<script>alert(123)</script>",
                '[removed]alert&#40;123&#41;[removed]'
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
                "<a href=\"\\x0Bjavascript:javascript:alert(1)\" id=\"fuzzelement1\">test</a>",
                '<a>test</a>'
            ],
            [
                "src=JaVaSCript:prompt(132)",
                'src=[removed]prompt&#40;132&#41;'
            ],
            [
                "<script\\x3Etype=\"text/javascript\">javascript:alert(1);</script>",
                '[removed][removed]alert&#40;1&#41;;[removed]'
            ],
            [
                "ABC<div style=\"x\\x3Aexpression(javascript:alert(1)\">DEF",
                'ABC<div>DEF'
            ],
            [
                "ABC<div style=\"x:\\xE2\\x80\\x84expression(javascript:alert(1)\">DEF",
                'ABC<div>DEF'
            ],
            [
                "<a href=\"\\xE2\\x80\\x88javascript:javascript:alert(1)\" id=\"fuzzelement1\">test</a>",
                '<a>test</a>'
            ],
            [
                '&nbsp;',
                '&nbsp;'
            ],
            [
                '',
                ''
            ],
            [
                '<a>',
                '<a>'
            ],
            [
                "{{ \"\".__class__.__mro__[2].__subclasses__()[40](\"/etc/passwd\").read() }}",
                '{{ "".__class__.__mro__[2].__subclasses__()[40]("/etc/passwd").read() }}'
            ],
            [
                "But now...\u001b[20Cfor my greatest trick...\u001b[8m",
                'But now...\u001b[20Cfor my greatest trick...\u001b[8m'
            ],
            [
                "Scunthorpe General Hospital",
                'Scunthorpe General Hospital'
            ],
            [
                "<<< %s(un='%s') = %u",
                '&lt;&lt;&lt; %s(un=\'%s\') = %u'
            ],
            [
                "%s%s%s%s%s",
                '%s%s%s%s%s'
            ],
            [
                "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [ <!ELEMENT foo ANY ><!ENTITY xxe SYSTEM \"file:///etc/passwd\" >]><foo>&xxe;</foo>",
                '&lt;?xml version="1.0" encoding="ISO-8859-1"?&gt;&lt;!DOCTYPE foo [ &lt;!ELEMENT foo ANY >&lt;!ENTITY xxe SYSTEM "file:///etc/passwd" >]><foo>&xxe;</foo>'
            ],
            [
                "eval(\"puts 'hello world'\")",
                'eval&#40;"puts \'hello world\'"&#41;'
            ],
            [
                "`touch /tmp/blns.fail`",
                '`touch /tmp/blns.fail`'
            ],
            [
                "/dev/null; touch /tmp/blns.fail ; echo",
                '/dev/null; touch /tmp/blns.fail ; echo'
            ],
            [
                "'; EXEC sp_MSForEachTable 'DROP TABLE ?'; --",
                '\'; EXEC sp_MSForEachTable \'DROP TABLE ?\'; --'
            ],
            [
                "1'; DROP TABLE users-- 1",
                '1\'; DROP TABLE users-- 1'
            ],
            [
                '1;DROP TABLE users',
                '1;DROP TABLE users'
            ],
            [
                "</textarea><script>alert(123)</script>",
                '&lt;/textarea&gt;[removed]alert&#40;123&#41;[removed]'
            ],
            [
                "http://a/%%30%30",
                'http://a/'
            ],
            [
                "<u oncopy=alert()> Copy me</u>",
                '<u> Copy me</u>'
            ],
            [
                "\\\";alert('XSS');//",
                '\";alert&#40;\'XSS\'&#41;;//'
            ],
            [
                "<iframe src=http://ha.ckers.org/scriptlet.html <",
                '&lt;iframe src=http://ha.ckers.org/scriptlet.html <'
            ],
            [
                "<<SCRIPT>alert(\"XSS\");//<</SCRIPT>",
                '&lt;[removed]alert&#40;"XSS"&#41;;//&lt;[removed]'
            ],
            [
                "<SCRIPT/SRC=\"http://ha.ckers.org/xss.js\"></SCRIPT>",
                '[removed][removed]'
            ],
            [
                "<BODY onload!#$%&()*~+-_.,:;?@[/|\\]^`=alert(\"XSS\")>",
                '&lt;BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert&#40;"XSS"&#41;&gt;'
            ],
            [
                "<IMG SRC=\" &#14;  javascript:alert('XSS');\">",
                '<IMG \'>'
            ],
            [
                "perl -e 'print \"<IMG SRC=java\\0script:alert(\\\"XSS\\\")>\";' > out",
                'perl -e \'print "<IMG >";\' > '
            ],
            [
                "<IMG SRC=\"jav&#x09;ascript:alert('XSS');\">",
                '<IMG \'>'
            ],
            [
                "<IMG SRC=\"jav   ascript:alert('XSS');\">",
                '<IMG >'
            ],
            [
                "<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#"
                . "116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>",
                '<IMG >'
            ],
            [
                "<script src=\"\\\\%(jscript)s\"></script>",
                '[removed][removed]'
            ],
            [
                "<script src=\"/\\%(jscript)s\"></script>",
                '[removed][removed]'
            ],
            [
                "<!--[if<img src=x onerror=javascript:alert(1)//]> -->",
                '&lt;!--[if<img > --&'
            ],
            [
                "<!--[if]><script>javascript:alert(1)</script -->",
                '&lt;!--[if]>[removed][removed]alert&#40;1&#41;&lt;/script --&gt;'
            ],
            [
                "<a href=http://foo.bar/#x=`y></a><img alt=\"`><img src=x:x onerror=javascript:alert(1)></a>\">",
                '<a ></a><img>'
            ],
            [
                "<title onpropertychange=javascript:alert(1)></title><title title=>",
                '&lt;title onpropertychange=[removed]alert&#40;1&#41;&gt;&lt;/title&gt;&lt;title title=&gt;'
            ],
            [
                "<a href=java&nbsp&#1&#2&#3&#4&#5&#6&#7&#8&#11&#12script:javascript:alert(1)>XXX</a>",
                '<a >XXX</a>'
            ],
            [
                "<img \\x47src=x onerror=\"javascript:alert(1)\">",
                '<img>'
            ],
            [
                "\"`'><script>\\xE2\\x80\\x89javascript:alert(1)</script>",
                '"`\'>[removed]\xE2\x80\x89[removed]alert&#40;1&#41;[removed]'
            ],
            [
                "`\"'><img src=xxx:x \\x0Aonerror=javascript:alert(1)>",
                '`"\'><img >'
            ],
            [
                "<a href=\"\\xE1\\x9A\\x80javascript:javascript:alert(1)\" id=\"fuzzelement1\">test</a>",
                '<a>test</a>'
            ],
            [
                ";alert(123);",
                ';alert&#40;123&#41;;'
            ],
            [
                "'><script>alert(123)</script>",
                '\'>[removed]alert&#40;123&#41;[removed]'
            ],
            [
                '"\" onfocus=JaVaSCript:alert(123) autofocus", ',
                '"\" onfocus=[removed]alert&#40;123&#41; autofocus", '
            ],
            [
                'alert("hello");',
                'alert&#40;"hello"&#41;;'
            ],
            [
                " onfocus=JaVaSCript:alert(123) autofocus",
                ' onfocus=[removed]alert&#40;123&#41; autofocus'
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
                '<img>'
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
                '<tnh ->',
                '<tnh >'
            ],
            [
                '<?php eval("foo");',
                '&lt;?php eval&#40;"foo"&#41;;'
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error\Renderer;

use Platine\Framework\Handler\Error\Renderer\HtmlErrorRenderer;
use Platine\Test\Framework\Handler\Error\BaseErrorHandlerTestCase;

/*
 * @group core
 * @group framework
 */
class HtmlErrorRendererTest extends BaseErrorHandlerTestCase
{
    public function testRenderWithoutDetail(): void
    {
        $o = new HtmlErrorRenderer();
        $ex = $this->throwTestException();
        $expected = '<html><head><meta http-equiv="Content-Type" content="text/html; '
                . 'charset=utf-8"><title>Application Error</title><style>'
                . 'body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}'
                . 'h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}'
                . 'strong{display:inline-block;width:65px}</style></head><body>'
                . '<h1>Application Error</h1><div>'
                . '<p>An error has occurred. Sorry for the temporary inconvenience.</p>'
                . '</div><a href="#" onClick="window.history.go(-1)">Go Back</a></body></html>';
        $this->assertEquals($expected, $o->render($ex, false, false));
    }

    public function testRenderWithoutDetailAndLog(): void
    {
        $file = $this->getExceptionThrownFilePath();
        $o = new HtmlErrorRenderer();
        $ex = $this->throwTestException();
        $expected = 'Application Error: An error has occurred. Sorry for'
                . ' the temporary inconvenience., Exception(code:100) '
                . 'Foo exception 2 at ' . $file . ' line 23';
        $this->assertEquals($expected, $o->render($ex, false, true));
    }

    public function testRenderWithDetail(): void
    {
        global $mock_htmlentities_to_empty;
        $mock_htmlentities_to_empty = true;
        $file = $this->getExceptionThrownFilePath();
        $o = new HtmlErrorRenderer();
        $ex = $this->throwTestException();
        $expected = '<html><head><meta http-equiv="Content-Type" content="text/html; '
                . 'charset=utf-8"><title>Application Error</title>'
                . '<style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}'
                . 'h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}'
                . 'strong{display:inline-block;width:65px}</style></head><body>'
                . '<h1>Application Error</h1><div>'
                . '<p>The application could not run because of the following error:</p>'
                . '<h2>Details</h2><div><strong>Type:</strong> Exception</div>'
                . '<div><strong>Code:</strong> 100</div><div><strong>Message:</strong> '
                . '</div><div><strong>File:</strong> ' . $file
                . '</div><div><strong>Line:</strong> 23</div><h2>Trace</h2><pre></pre>'
                . '</div><a href="#" onClick="window.history.go(-1)">Go Back</a></body></html>';
        $this->assertEquals($expected, $o->render($ex, true, false));
    }

    public function testRenderHttpException(): void
    {
        global $mock_htmlentities_to_empty;
        $mock_htmlentities_to_empty = true;
        $file = $this->getExceptionThrownFilePath();
        $o = new HtmlErrorRenderer();
        $ex = $this->throwTestHttpException();
        $expected = '404 Not found: The requested resource [] could not be found. '
                . 'Please verify the URI and try again, '
                . 'Platine\Framework\Http\Exception\HttpNotFoundException(code:404) '
                . 'not found at ' . $file . ' line 39';
        $this->assertEquals($expected, $o->render($ex, true, true));
    }
}

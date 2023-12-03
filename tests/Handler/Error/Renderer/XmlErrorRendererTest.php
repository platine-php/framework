<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error\Renderer;

use Platine\Framework\Handler\Error\Renderer\XmlErrorRenderer;
use Platine\Test\Framework\Handler\Error\BaseErrorHandlerTestCase;

/*
 * @group core
 * @group framework
 */
class XmlErrorRendererTest extends BaseErrorHandlerTestCase
{
    public function testRender(): void
    {
        $o = new XmlErrorRenderer();
        $ex = $this->throwTestException();
        $file = $this->getExceptionThrownFilePath(false);
        $expected = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<error><message><![CDATA[Foo exception 2]]></message><exceptions><exception>'
                . '<type>Exception</type><code>100</code><message><![CDATA[Foo exception 2]]>'
                . '</message><file>' . $file . '</file>'
                . '<line>23</line></exception><exception><type>Exception</type><code>0</code>'
                . '<message><![CDATA[Foo exception 1]]></message><file>' . $file . '</file>'
                . '<line>20</line></exception></exceptions></error>';
        $this->assertEquals($expected, $o->render($ex, true, false));
    }
}

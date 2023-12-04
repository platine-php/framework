<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error\Renderer;

use Platine\Framework\Handler\Error\Renderer\TextPlainErrorRenderer;
use Platine\Test\Framework\Handler\Error\BaseErrorHandlerTestCase;

/*
 * @group core
 * @group framework
 */
class TextPlainErrorRendererTest extends BaseErrorHandlerTestCase
{
    public function testRender(): void
    {
        $o = new TextPlainErrorRenderer();
        $ex = $this->throwTestException();
        $file = $this->getExceptionThrownFilePath(false);
        $expected = 'Application Error
Type: Exception
Code: 100
Message: Foo exception 2
File: ' . $file . '
Line: 23

Previous Error:
Type: Exception
Code: 0
Message: Foo exception 1
File: ' . $file . '
Line: 20
';
        $this->assertCommandOutput($expected, $o->render($ex, true, false));
    }

    public function testRenderWithoutDetail(): void
    {
        $o = new TextPlainErrorRenderer();
        $ex = $this->throwTestException();
        $expected = 'Application Error
';
        $this->assertCommandOutput($expected, $o->render($ex, false, false));
    }

    public function testRenderForLog(): void
    {
        $o = new TextPlainErrorRenderer();
        $ex = $this->throwTestException();
        $expected = 'Application Error
';
        $this->assertCommandOutput($expected, $o->render($ex, false, true));
    }
}

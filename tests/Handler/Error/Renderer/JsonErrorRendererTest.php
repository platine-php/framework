<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error\Renderer;

use Platine\Framework\Handler\Error\Renderer\JsonErrorRenderer;
use Platine\Test\Framework\Handler\Error\BaseErrorHandlerTestCase;

/*
 * @group core
 * @group framework
 */
class JsonErrorRendererTest extends BaseErrorHandlerTestCase
{

    public function testRender(): void
    {
        $o = new JsonErrorRenderer();
        $ex = $this->throwTestException();
        $file = $this->getExceptionThrownFilePath();
        $expected = '{
    "message": "Platine Application Error",
    "exception": [
        {
            "type": "Exception",
            "code": 100,
            "message": "Foo exception 2",
            "file": "' . $file . '",
            "line": 24
        },
        {
            "type": "Exception",
            "code": 0,
            "message": "Foo exception 1",
            "file": "' . $file . '",
            "line": 21
        }
    ]
}';
        $this->assertEquals($expected, $o->render($ex, true, false));
    }
}

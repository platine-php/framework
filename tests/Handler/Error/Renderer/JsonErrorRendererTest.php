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
        $ex = $this->throwTestHttpException();
        $file = $this->getExceptionThrownFilePath(true);
        $expected = <<<E
{
    "title": "404 Not found",
    "message": "The requested resource [] could not be found. Please verify the URI and try again",
    "request_id": "",
    "exception": [
        {
            "type": "Platine\\\Framework\\\Http\\\Exception\\\HttpNotFoundException",
            "code": 404,
            "message": "not found",
            "file": "$file",
            "line": 39
        },
        {
            "type": "Exception",
            "code": 0,
            "message": "Foo exception 1",
            "file": "$file",
            "line": 36
        }
    ]
}
E;

        $this->assertCommandOutput($expected, $o->render($ex, true, false));
    }
}

<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Template\Tag\AuthTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class AuthTagTest extends PlatineTestCase
{
    public function testRenderNotLogin(): void
    {
        global $mock_app_auth_object,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_auth_object = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => false
        ]);


        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endauth %}'];
        $b = new AuthTag('foo', $tokens, $parser);

        $c = new Context();
        $res = $b->render($c);
        $this->assertEmpty($res);
    }

    public function testRender(): void
    {
        global $mock_app_auth_object,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_auth_object = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => true
        ]);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endauth %}'];
        $b = new AuthTag('foo', $tokens, $parser);

        $c = new Context();
        $res = $b->render($c);
        $this->assertEquals('tnh', $res);
    }
}

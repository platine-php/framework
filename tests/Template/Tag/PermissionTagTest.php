<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authorization\SessionAuthorization;
use Platine\Framework\Template\Tag\PermissionTag;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class PermissionTagTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endpermission %}'];
        $b = new PermissionTag('permission', $tokens, $parser);

        $this->assertEquals('permission', $this->getPropertyValue(PermissionTag::class, $b, 'permission'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new PermissionTag('(+', $tokens, $parser));
    }

    public function testRenderNoPermission(): void
    {
        global $mock_app_auth_object,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_auth_object = $this->getMockInstance(SessionAuthorization::class, [
            'isGranted' => false
        ]);

        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endpermission %}'];
        $b = new PermissionTag('permission', $tokens, $parser);

        $c = new Context();
        $res = $b->render($c);
        $this->assertEmpty($res);
    }

    public function testRender(): void
    {
        global $mock_app_auth_object,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_auth_object = $this->getMockInstance(SessionAuthorization::class, [
            'isGranted' => true
        ]);

        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endpermission %}'];
        $b = new PermissionTag('permission', $tokens, $parser);

        $c = new Context();
        $res = $b->render($c);
        $this->assertEquals('tnh', $res);
    }

    public function testRenderPermissionCodeIsFromContext(): void
    {
        global $mock_app_auth_object,
           $mock_app_to_instance;

        $mock_app_to_instance = true;
        $mock_app_auth_object = $this->getMockInstance(SessionAuthorization::class, [
            'isGranted' => true
        ]);

        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endpermission %}'];
        $b = new PermissionTag('permission', $tokens, $parser);

        $c = new Context();
        $c->set('permission', 'foo');

        $res = $b->render($c);
        $this->assertEquals('tnh', $res);
    }
}

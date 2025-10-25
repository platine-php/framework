<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authorization\DefaultAuthorization;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Framework\Template\Tag\RouteUrlTag;
use Platine\Route\Route;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/*
 * @group core
 * @group framework
 */
class RouteUrlTagTest extends PlatineTestCase
{
    public function testConstructWrongSynthax(): void
    {
        $parser = $this->getMockInstance(Parser::class);

        $tokens = [];
        $this->expectException(ParseException::class);
        (new RouteUrlTag('', $tokens, $parser));
    }




    public function testRenderNeedAuthButNotGranted(): void
    {
        $this->render(true, false);
    }

    public function testRenderNeedAuthGranted(): void
    {
        $this->render(true, true);
    }


    protected function render(bool $needAuth = true, bool $isGranted = false): void
    {
        global $mock_app_to_instance,
               $mock_app_router_methods,
               $mock_app_auth_object,
               $mock_app_csrfmanager_object,
               $mock_app_route_helper_methods;


        $mock_app_to_instance = true;

        $mock_app_route_helper_methods = [
            'generateUrl' => 'http://localhost'
        ];
        $mock_app_router_methods = [
            'routes' => [new Route(
                '/users/{id}',
                'foo',
                'user_detail',
                [],
                ['permission' => $needAuth ? 'user_create' : null, 'csrf' => true]
            )],
        ];

        $mock_app_auth_object = $this->getMockInstance(DefaultAuthorization::class, [
            'isGranted' => $isGranted
        ]);

        $mock_app_csrfmanager_object = $this->getMockInstance(CsrfManager::class, [
            'getTokenQuery' => ['_token' => 'csrftoken']
        ]);

        $context = new Context(['route_var' => 'user_detail']);

        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $o = new RouteUrlTag('route_var id:1', $tokens, $parser);

        if ($needAuth && $isGranted === false) {
            $this->assertEquals('#hide', $o->render($context));
        } else {
            $this->assertEquals('http://localhost?_token=csrftoken', $o->render($context));
        }
    }
}

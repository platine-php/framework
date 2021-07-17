<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;

/**
 * Description of LogoutAction
 *
 * @author tony
 */
class LogoutAction implements RequestHandlerInterface
{

    protected RouteHelper $routeHelper;
    protected AuthenticationInterface $authentication;

    public function __construct(
        AuthenticationInterface $authentication,
        RouteHelper $routeHelper
    ) {
        $this->routeHelper = $routeHelper;
        $this->authentication = $authentication;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->authentication->logout();

        $res = new Response();

        $loginUrl = $this->routeHelper->generateUrl('user_login');

        $res->getBody()
                ->write(sprintf('You are successfully logout 
                    <br /><a href = \'%s\'>Login Page</a>', $loginUrl));

        return $res;
    }
}

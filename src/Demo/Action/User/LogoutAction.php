<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Session\Session;

/**
 * Description of LogoutAction
 *
 * @author tony
 */
class LogoutAction implements RequestHandlerInterface
{

    protected Session $session;
    protected RouteHelper $routeHelper;

    public function __construct(
        Session $session,
        RouteHelper $routeHelper
    ) {
        $this->session = $session;
        $this->routeHelper = $routeHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->remove('user');
        $res = new Response();

        $loginUrl = $this->routeHelper->generateUrl('user_login');

        $res->getBody()
                ->write(sprintf('You are successfully logout 
                    <br /><a href = \'%s\'>Login Page</a>', $loginUrl));

        return $res;
    }
}

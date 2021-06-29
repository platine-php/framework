<?php

namespace Platine\Framework\Demo\Action\User;

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

    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->remove('user');

        $res = new Response();

        $res->getBody()
                ->write('You are successfully logout 
                    <br /><a href = \'login\'>Login Page</a>');

        return $res;
    }
}

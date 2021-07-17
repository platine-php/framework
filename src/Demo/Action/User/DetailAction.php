<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * Description of DetailAction
 *
 * @author tony
 */
class DetailAction implements RequestHandlerInterface
{

    protected LoggerInterface $logger;
    protected UserRepository $userRepository;
    protected Template $template;
    protected RouteHelper $routeHelper;
    protected Session $session;


    public function __construct(
        Session $session,
        LoggerInterface $logger,
        Template $template,
        UserRepository $userRepository,
        RouteHelper $routeHelper
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $user = $this->userRepository
                     ->with('roles')
                     ->find($id);
        if (!$user) {
            $this->session->setFlash('error', 'Can not find the user');
            $this->logger->warning('Can not find user with id {id}', ['id' => $id]);

            return new RedirectResponse(
                $this->routeHelper->generateUrl('user_list')
            );
        }

        return new TemplateResponse(
            $this->template,
            'user/detail',
            [
                'user' => $user,
            ]
        );
    }
}

<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * Description of DeleteAction
 *
 * @author tony
 */
class DeleteAction implements RequestHandlerInterface
{

    protected LoggerInterface $logger;
    protected UserRepository $userRepository;
    protected Template $template;
    protected RouteHelper $routeHelper;
    protected Session $session;
    protected Lang $lang;


    public function __construct(
        Lang $lang,
        Session $session,
        LoggerInterface $logger,
        Template $template,
        UserRepository $userRepository,
        RouteHelper $routeHelper
    ) {
        $this->lang = $lang;
        $this->logger = $logger;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $user = $this->userRepository->find($id);
        if (!$user) {
            $this->session->setFlash('error', $this->lang->tr('Can not find the user'));
            $this->logger->warning('Can not find user with id {id}', ['id' => $id]);

            return new RedirectResponse(
                $this->routeHelper->generateUrl('user_list')
            );
        }
        $this->logger->info('Delete of user {user}', ['user' => $user->id]);
        $this->userRepository->delete($user);

        $this->session->setFlash('success', 'User deleted successfully');

        return new RedirectResponse(
            $this->routeHelper->generateUrl('user_list')
        );
    }
}

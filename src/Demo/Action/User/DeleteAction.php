<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Framework\Demo\Response\RedirectResponse;
use Platine\Framework\Demo\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
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


    public function __construct(
        LoggerInterface $logger,
        Template $template,
        UserRepository $userRepository
    ) {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $user = $this->userRepository->find($id);
        if (!$user) {
            $this->logger->warning('Can not find user with id {id}', ['id' => $id]);

            return (new RedirectResponse('../list'))->redirect();
        }
        $this->logger->info('Delete of user {user}', ['user' => $user->user_id]);
        $this->userRepository->delete($user);

        return (new RedirectResponse('../list'))->redirect();
    }
}

<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Framework\Demo\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Template\Template;

/**
 * Description of ListAction
 *
 * @author tony
 */
class ListAction implements RequestHandlerInterface
{
    protected UserRepository $userRepository;
    protected Template $template;

    public function __construct(
        Template $template,
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->userRepository->all();

        return new TemplateResponse(
            $this->template,
            'user/list',
            [
                'users' => $users,
            ]
        );
    }
}

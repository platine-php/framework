<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Demo\Form\Param\UserParam;
use Platine\Framework\Demo\Form\Validator\UserValidator;
use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Framework\Demo\Response\RedirectResponse;
use Platine\Framework\Demo\Response\TemplateResponse;
use Platine\Framework\Http\RequestData;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Security\Hash\BcryptHash;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * Description of CreateAction
 *
 * @author tony
 */
class CreateAction implements RequestHandlerInterface
{

    protected LoggerInterface $logger;
    protected Session $session;
    protected UserRepository $userRepository;
    protected Template $template;


    public function __construct(
        LoggerInterface $logger,
        Session $session,
        Template $template,
        UserRepository $userRepository
    ) {
        $this->logger = $logger;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'user/create',
                [
                    'param' => new UserParam([])
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new UserParam($param->posts());
        $validator = new UserValidator($formParam);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'user/create',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam
                ]
            );
        }

        $username = $param->post('username');
        $userExist = $this->userRepository->findBy(['username' => $username]);

        if ($userExist) {
            $this->logger->error('User with username {username} already exists', ['username' => $username]);
            return new TemplateResponse(
                $this->template,
                'user/create',
                [
                   'param' => $formParam
                ]
            );
        }

        $password = $param->post('password');

        $hash = new BcryptHash();
        $passwordHash = $hash->hash($password);

        $user = $this->userRepository->create([
            'username' => $formParam->getUsername(),
            'fname' => $formParam->getFirstname(),
            'lname' => $formParam->getLastname(),
            'password' => $passwordHash,
            'status' => 1,
            'age' => (int) $formParam->getAge(),
            'deleted' => 0,
        ]);

        $this->userRepository->save($user);

        return (new RedirectResponse('list'))->redirect();
    }
}
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
use Platine\Stdlib\Helper\Str;
use Platine\Template\Template;

/**
 * Description of EditAction
 *
 * @author tony
 */
class EditAction implements RequestHandlerInterface
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
        $id = (int) $request->getAttribute('id');
        $user = $this->userRepository->find($id);
        if (!$user) {
            $this->logger->warning('Can not find user with id {id}', ['id' => $id]);

            return new RedirectResponse('../list');
        }

        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                    'param' => (new UserParam())->fromEntity($user)
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new UserParam($param->posts());
        $validator = new UserValidator($formParam);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam
                ]
            );
        }

        $username = $param->post('username');
        $userExist = $this->userRepository->findBy(['username' => $username]);

        if ($userExist && $userExist->user_id != $id) {
            $this->logger->error('User with username {username} already exists', ['username' => $username]);
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                   'param' => $formParam
                ]
            );
        }

        $password = $param->post('password');

        $user->username = $formParam->getUsername();
        $user->fname = Str::ucfirst($formParam->getFirstname());
        $user->lname = Str::upper($formParam->getLastname());
        $user->age = (int) $formParam->getAge();

        if (!empty($password)) {
            $hash = new BcryptHash();
            $passwordHash = $hash->hash($password);
            $user->password = $passwordHash;
        }

        $result = $this->userRepository->save($user);

        if (!$result) {
            $this->logger->error('Error when saved the user');
            return new TemplateResponse(
                $this->template,
                'user/edit',
                [
                   'param' => $formParam
                ]
            );
        }

        return new RedirectResponse('../list');
    }
}

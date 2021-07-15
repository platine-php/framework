<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Demo\Form\Param\AuthParam;
use Platine\Framework\Demo\Form\Validator\AuthValidator;
use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Framework\Demo\Response\RedirectResponse;
use Platine\Framework\Demo\Response\TemplateResponse;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Security\Hash\BcryptHash;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * Description of LoginAction
 *
 * @author tony
 */
class LoginAction implements RequestHandlerInterface
{

    protected LoggerInterface $logger;
    protected Session $session;
    protected UserRepository $userRepository;
    protected Template $template;
    protected RouteHelper $routeHelper;

    public function __construct(
        LoggerInterface $logger,
        Session $session,
        Template $template,
        UserRepository $userRepository,
        RouteHelper $routeHelper
    ) {
        $this->logger = $logger;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'user/login',
                [
                    'param' => new AuthParam([])
                ]
            );
        }

        $param = new RequestData($request);
        $formParam = new AuthParam($param->posts());
        $validator = new AuthValidator($formParam);

        if (!$validator->validate()) {
            return new TemplateResponse(
                $this->template,
                'user/login',
                [
                    'errors' => $validator->getErrors(),
                    'param' => $formParam
                ]
            );
        }

        $username = $param->post('username');
        $password = $param->post('password');
        $user = $this->userRepository->findBy(['username' => $username]);

        if (!$user) {
            $this->session->setFlash('error', 'Can not find user with given username');
            return new TemplateResponse(
                $this->template,
                'user/login',
                [
                   'param' => $formParam
                ]
            );
        }

        $hash = new BcryptHash();
        if (!$hash->verify($password, $user->password)) {
            $this->logger->error('Invalid user credential, {username}', ['username' => $username]);
            $this->session->setFlash('error', 'Invalid user credential');
            return new TemplateResponse(
                $this->template,
                'user/login',
                [
                   'param' => $formParam
                ]
            );
        }

        $data = [
          'id' => $user->user_id,
          'username' => $user->username,
          'lastname' => $user->lname,
          'firstname' => $user->fname,
        ];
        $this->session->set('user', $data);

        return new RedirectResponse(
            $this->routeHelper->generateUrl('home')
        );
    }
}

<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\AuthenticationException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Demo\Form\Param\AuthParam;
use Platine\Framework\Demo\Form\Validator\AuthValidator;
use Platine\Framework\Http\RequestData;
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
 * Description of LoginAction
 *
 * @author tony
 */
class LoginAction implements RequestHandlerInterface
{

    protected LoggerInterface $logger;
    protected Template $template;
    protected RouteHelper $routeHelper;
    protected AuthenticationInterface $authentication;
    protected Session $session;

    public function __construct(
        AuthenticationInterface $authentication,
        Session $session,
        LoggerInterface $logger,
        Template $template,
        RouteHelper $routeHelper
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
        $this->authentication = $authentication;
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

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        try {
            $this->authentication->login($credentials);
        } catch (
            MissingCredentialsException
                | AccountNotFoundException
                | InvalidCredentialsException
                | AccountLockedException $ex
        ) {
            return $this->authExceptionResponse($ex, $formParam);
        }

        return new RedirectResponse(
            $this->routeHelper->generateUrl('home')
        );
    }

    /**
     * Return response when authentication throw Exception
     * @param AuthenticationException $ex
     * @param AuthParam $formParam
     * @return ResponseInterface
     */
    protected function authExceptionResponse(
        AuthenticationException $ex,
        AuthParam $formParam
    ): ResponseInterface {
        $this->logger->error('Authentication Error', ['exception' => $ex]);
        $this->session->setFlash('error', $ex->getMessage());
        return new TemplateResponse(
            $this->template,
            'user/login',
            [
               'param' => $formParam
            ]
        );
    }
}

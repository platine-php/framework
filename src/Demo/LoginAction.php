<?php

namespace Platine\Framework\Demo;

use Platine\Cache\CacheInterface;
use Platine\Config\Config;
use Platine\Cookie\CookieManagerInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Framework\Http\RequestData;
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
    protected Application $app;
    protected Config $config;
    protected Session $session;
    protected UserRepository $userRepository;
    protected Template $template;
    protected CookieManagerInterface $cookieManager;
    protected CacheInterface $cache;


    public function __construct(
        LoggerInterface $logger,
        Application $app,
        Config $config,
        Session $session,
        Template $template,
        UserRepository $userRepository,
        CookieManagerInterface $cookieManager,
        CacheInterface $cache
    ) {
        $this->logger = $logger;
        $this->app = $app;
        $this->config = $config;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->template = $template;
        $this->cookieManager = $cookieManager;
        $this->cache = $cache;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        if ($request->getMethod() === 'GET') {
            return new TemplateResponse(
                $this->template,
                'login',
                []
            );
        }

        $param = new RequestData($request);


        $name = $param->post('name');
        $user = $this->userRepository->findBy(['lname' => $name]);

        if (!$user) {
            $this->logger->error('User with name {name} not found', ['name' => $name]);
            return new TemplateResponse(
                $this->template,
                'login',
                [
                   'name' => $name
                ]
            );
        }

        $this->session->set('user', $user);

        return (new RedirectResponse('home'))->redirect();
    }
}

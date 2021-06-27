<?php

namespace Platine\Framework\Demo;

use Platine\Cache\CacheInterface;
use Platine\Config\Config;
use Platine\Cookie\CookieManagerInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * Description of HomeAction
 *
 * @author tony
 */
class HomeAction implements RequestHandlerInterface
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
        $user = $this->session->get('user');
        if (!$user) {
            $this->logger->info('User ot yet login');

            return (new RedirectResponse('login'))->redirect();
        }

        return new TemplateResponse(
            $this->template,
            'home',
            [
                'user' => $user,
            ]
        );
    }
}

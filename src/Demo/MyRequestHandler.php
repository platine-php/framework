<?php

namespace Platine\Framework\Demo;

use Platine\Config\Config;
use Platine\Cookie\Cookie;
use Platine\Cookie\CookieManagerInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Demo\Repository\UserRepository;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Session\Session;
use Platine\Template\Template;

/**
 * Description of MyRequestHandler
 *
 * @author tony
 */
class MyRequestHandler implements RequestHandlerInterface
{

    protected LoggerInterface $logger;
    protected Application $app;
    protected Config $config;
    protected Session $session;
    protected UserRepository $userRepository;
    protected Template $template;
    protected CookieManagerInterface $cookieManager;


    public function __construct(
        LoggerInterface $logger,
        Application $app,
        Config $config,
        Session $session,
        Template $template,
        UserRepository $userRepository,
        CookieManagerInterface $cookieManager
    ) {
        $this->logger = $logger;
        $this->app = $app;
        $this->config = $config;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->template = $template;
        $this->cookieManager = $cookieManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        //$param = new RequestData($request);

        if (!$this->session->has('lang')) {
            $this->logger->info('Session language not exist create it now');
            $this->session->set('lang', 'fr_Fr');
        }
        $lang = $this->session->get('lang');

        $name = $request->getAttribute('name', 'Tony');
        $user = $this->userRepository->find(1);
        //$this->logger->info('User is {user}', ['user' => print_r($user, true)]);
        //$this->cookieManager->add(new Cookie('app_user', print_r($user, true)));
        $this->logger->info('User name is {name}', ['name' => $name]);
        $resp = new Response(200);

        $content = $this->template->render('home', [
            'name' => $name,
            'lang' => $lang,
            'user' => $user,
            'app_name' => $this->config->get('app.name'),
            'version' => $this->app->version(),
        ]);

        $resp->getBody()->write($content);

        return $resp->withHeader('Framework', 'Platine PHP');
    }
}

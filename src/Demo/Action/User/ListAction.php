<?php

namespace Platine\Framework\Demo\Action\User;

use Platine\Framework\Config\DbConfig;
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
    protected DbConfig $dbConfig;

    public function __construct(
        Template $template,
        UserRepository $userRepository,
        DbConfig $dbConfig
    ) {
        $this->userRepository = $userRepository;
        $this->template = $template;
        $this->dbConfig = $dbConfig;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = $this->dbConfig->get('app.name');

        var_dump($name);
        /*$debug = $this->dbConfig->get('app.debug');
        $prefix = $this->dbConfig->get('app.billing.invoice.prefix');
        echo $name.'|'.$debug.'|'.$prefix;
         *
         */
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

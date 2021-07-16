<?php

namespace Platine\Framework\Demo\Action;

use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Template\Template;

/**
 * Description of HomeAction
 *
 * @author tony
 */
class HomeAction implements RequestHandlerInterface
{
    protected Template $template;

    public function __construct(
        Template $template
    ) {
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TemplateResponse(
            $this->template,
            'home',
            []
        );
    }
}

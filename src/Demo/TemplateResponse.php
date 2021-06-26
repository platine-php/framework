<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine\Framework\Demo;

use Platine\Http\Response;
use Platine\Template\Template;

/**
 * Description of TemplateResponse
 *
 * @author tony
 */
class TemplateResponse extends Response
{

    protected Template $template;

    /**
     * Create new instance
     * @param Template $template
     * @param string $name
     * @param array<string, mixed> $context
     * @param int $statusCode
     * @param string $reasonPhrase
     */
    public function __construct(
        Template $template,
        string $name,
        array $context = [],
        int $statusCode = 200,
        string $reasonPhrase = ''
    ) {
        parent::__construct($statusCode, $reasonPhrase);
        $this->getBody()->write($template->render($name, $context));
    }

    /**
     * Return the template instance
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }
}

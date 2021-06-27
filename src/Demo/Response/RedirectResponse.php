<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine\Framework\Demo\Response;

use Platine\Http\Response;

/**
 * Description of RedirectResponse
 *
 * @author tony
 */
class RedirectResponse extends Response
{

    /**
     * The URL to redirect to
     * @var string
     */
    protected string $url = '/';

    /**
     * Create new instance
     * @param string $url
     */
    public function __construct(string $url = '/')
    {
        parent::__construct(301);
        $this->url = $url;
    }

    /**
     * Redirect to the current URL attribute
     * @return $this
     */
    public function redirect(): self
    {
        return $this->withHeader('Location', $this->url);
    }
}

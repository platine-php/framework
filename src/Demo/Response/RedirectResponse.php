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
     * Create new instance
     * @param string $url
     */
    public function __construct(string $url = '/')
    {
        parent::__construct(302);
        $this->headers['Location'] = [$url];
    }
}

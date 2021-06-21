<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine;

use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;

/**
 * Description of MyRequestHandler
 *
 * @author tony
 */
class MyRequestHandler implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        if (!$id) {
            $resp = new Response(200);
            $resp->getBody()->write('My first request handler');
            return $resp;
        } else {
            return (new Response(405))->withHeader('Allow', 'GET, POST');
        }
    }
}

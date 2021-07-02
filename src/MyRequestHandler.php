<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine\Framework;

use Platine\Framework\Http\RequestData;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;

/**
 * Description of MyRequestHandler
 *
 * @author tony
 */
class MyRequestHandler implements RequestHandlerInterface
{

    protected LoggerInterface $logger;

    /**
     * Create new instance
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //$param = new RequestData($request);

        $name = $request->getAttribute('name', 'Tony');
        $this->logger->info('User name is {name}', ['name' => $name]);
        $resp = new Response(200);
        $resp->getBody()->write("Hello ${name}");

        return $resp->withHeader('Framework', 'Platine PHP');
    }
}

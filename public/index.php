<?php
require __DIR__ . '/../../../vendor/autoload.php';


use Platine\Framework\App\Application;
use Platine\Framework\Kernel\HttpKernel;

$app = new Application();
$app->setConfigPath(__DIR__ . '/../config');

$kernel = $app->make(HttpKernel::class); 

$kernel->use(function($req, $h){
    return $h->handle($req);
});

$kernel->run();
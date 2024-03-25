<?php

use DI\ContainerBuilder;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container.php');
$container = $containerBuilder->build();

$app = $container->get(App::class);


$app->add('src\middleware\BreadcrumbsMiddleware');


$app->get('/', 'src\controller\HomeController:index');
$app->get('/item/{id}', 'src\controller\HomeController:getAnnonce');


return $app;

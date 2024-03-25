<?php

use Slim\App;
use Psr\Container\ContainerInterface;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Loader\FilesystemLoader;

if (!file_exists(__DIR__ . '/../config/config.ini')) {
    throw new Exception('The config.ini file is missing');
}
$ini = parse_ini_file(__DIR__ . '/../config/config.ini');

return [
    'settings' => [
        'displayErrorDetails' => true,
        'database' => $ini
    ],
    App::class => function (ContainerInterface $container) {
        $factory = new ConnectionFactory(new IlluminateContainer());
        $connection = $factory->make($container->get('settings')['database']);
        $resolver = new ConnectionResolver();
        $resolver->addConnection('default', $connection);
        $resolver->setDefaultConnection('default');
        Model::setConnectionResolver($resolver);

        AppFactory::setContainer($container);

        return AppFactory::create();
    },
    Twig::class => function (ContainerInterface $container) {
        $loaderInterface = new FilesystemLoader(__DIR__ . '/../template');
        return new Twig($loaderInterface, [
            'cache' => false,
        ]);
    },
    TwigMiddleware::class => function (ContainerInterface $container) {
        return TwigMiddleware::createFromContainer($container->get(App::class));
    }
];

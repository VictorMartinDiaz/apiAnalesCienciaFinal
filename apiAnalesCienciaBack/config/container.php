<?php

use Doctrine\ORM\EntityManager;
use TDW\ACiencia\Auth\JwtAuth;
use TDW\ACiencia\Factory\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Selective\BasePath\BasePathMiddleware;
use Selective\Config\Configuration;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use TDW\ACiencia\Utility\Utils;

return [
    // Application settings
    Configuration::class => function () {
        return new Configuration(require __DIR__ . '/settings.php');
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $config = $container->get(Configuration::class);
        $routeCacheFile = $config->findString('router.cache_file');
        if ($routeCacheFile) {
            $app->getRouteCollector()->setCacheFile($routeCacheFile);
        }

        return $app;
    },

    // For the responder
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    // The Slim RouterParser
    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },

    // The logger factory
    LoggerFactory::class => function (ContainerInterface $container) {
        return new LoggerFactory($container->get(Configuration::class)->getArray('logger'));
    },

    BasePathMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);

        return new BasePathMiddleware($app);
    },

    EntityManager::class => Utils::getEntityManager(),

    // And add this entry
    JwtAuth::class => function (ContainerInterface $container) {
        $config = $container->get(Configuration::class);

        $issuer = $config->getString('jwt.issuer');
        $clientId = $config->getString('jwt.client-id');
        $lifetime = $config->getInt('jwt.lifetime');
        $privateKey = $config->getString('jwt.private_key');
        $publicKey = $config->getString('jwt.public_key');

        return new JwtAuth($issuer, $clientId, $lifetime, $privateKey, $publicKey);
    },
];

<?php
namespace MPULL\DockerDemo;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Tools\Setup;
use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };
    // Doctrine
    $container[EntityManager::class] = function (\Slim\Container $container): EntityManager {
        $config = Setup::createAnnotationMetadataConfiguration(
            $container['settings']['doctrine']['metadata_dirs'],
            $container['settings']['doctrine']['dev_mode']
        );

        $config->setMetadataDriverImpl(
            new AnnotationDriver(
                new AnnotationReader,
                $container['settings']['doctrine']['metadata_dirs']
            )
        );

        $config->setMetadataCacheImpl(
            new FilesystemCache(
                $container['settings']['doctrine']['cache_dir']
            )
        );

        return EntityManager::create(
            $container['settings']['doctrine']['connection'],
            $config
        );
    };
};

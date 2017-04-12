<?php

use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SerializerServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Predis\Silex\ClientServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Provider\DoctrineServiceProvider;

$app->register(new ServiceControllerServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SerializerServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new CorsServiceProvider(), [
    'cors.allowOrigin' => [
        'localhost:9000',
        'http://localhost:9000'
    ]
]);


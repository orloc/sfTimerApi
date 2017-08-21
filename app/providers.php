<?php

use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SerializerServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Provider\DoctrineServiceProvider;

$app->register(new ServiceControllerServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SerializerServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new CorsServiceProvider(), [
    'cors.allowOrigin' => [
        'http://eqtimers.orloc.me',
        'eqtimers.orloc.me',
        'localhost:9000',
        'http://localhost:9000'
    ]
]);


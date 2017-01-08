<?php

use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Predis\Silex\ClientServiceProvider;

$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new ClientServiceProvider(), [
    'predis.parameters' => 'tcp://127.0.0.1:6379',
    'predis.options'    => [
        'prefix'  => 'eqtimer:',
        'profile' => '3.0',
    ],
]);

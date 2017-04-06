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
$app->register(new AssetServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SerializerServiceProvider());

$app->register(new Silex\Provider\SecurityServiceProvider(), [
    'security.firewalls' => [
        'login' => [
            'pattern' => 'login|register',
            'anonymous' => true
        ],
        'main' => [
            'pattern' => '^/api',
            'stateless' => true,
            'logout' => [
                'logout_path' => '/logout' , 'invalidate_session' => true
            ],
            'guard' => [
                'authenticators' => [
                    'eqt.jwt_authenticator'
                ]
            ],
            'users' => function() use ($app){
                return new \EQT\Api\Security\UserProvider($app['db']);
            }
        ]
    ],
    'security.access_rules' => [
        ['^/token-group', 'ROLE_MEMBER']
    ],
    'security.role_hierarchy' => [
        'ROLE_ADMIN' => [ 'ROLE_USER' ]
    ]
]);
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new DoctrineServiceProvider(), [
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'eq_timers',
        'user'      => 'root',
        'password'  => ''
    ),
]);
/*
$app->register(new ClientServiceProvider(), [
    'predis.parameters' => 'tcp://127.0.0.1:6379',
    'predis.options'    => [
        'prefix'  => 'eqtimer:',
        'profile' => '3.0',
    ],
]);
*/

$app->register(new CorsServiceProvider(), [
    "cors.allowOrigin" => "*",
]);

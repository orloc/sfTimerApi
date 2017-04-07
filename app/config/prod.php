<?php

// configure your app for the production environment

$app['eqt.entity_class_path'] = 'EQT\Api\Entity';


$app['db.options'] = [
    'driver'    => 'pdo_mysql',
    'host'      => 'localhost',
    'dbname'    => 'eq_timers',
    'user'      => 'root',
    'password'  => ''
];

$app["cors.allowOrigin"] = "*";

$app['twig.path'] = [__DIR__.'/../templates'];
$app['twig.options'] = ['cache' => __DIR__.'/../var/cache/twig'];

$app['security.jwt'] = [
    'secret_key' => 'Very_secret_key',
    'life_time'  => 86400,
    'algorithm'  => ['HS256'],
    'options'    => [
        'username_claim' => 'name', // default name, option specifying claim containing username
        'header_name' => 'X-Access-Token', // default null, option for usage normal oauth2 header
        'token_prefix' => 'Bearer',
    ]
];

$app['security.access_rules'] = [
    [ '^/token-group', 'ROLE_MEMBER' ],
    [ '^/user', 'ROLE_ADMIN']
];

$app['security.role_hierarchy'] = [
    'ROLE_ADMIN' => [ 'ROLE_MEMBER' ],
];

$app['security.firewalls'] = [
    'login' => [
        'pattern' => 'login|register',
        'anonymous' => true
    ],
    'main' => [
        'pattern' => '^/api',
        'logout' => [ 'logout_path' => '/logout' ],
        'guard' => [
            'authenticators' => [
                'eqt.jwt_authenticator'
            ]
        ],
        'users' => function() use ($app){
            return new \EQT\Api\Security\UserProvider($app['db'], $app['eqt.models.user']);
        }
    ]
    
];


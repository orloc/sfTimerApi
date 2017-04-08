<?php

// configure your app for the production environment

$app['eqt.entity_class_path'] = 'EQT\Api\Entity';
$app['eqt.api.prefix'] = "/api/v1";

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
    'life_time'  => 60 * 60 * 3,
    'algorithm'  => ['HS256'],
    'options'    => [
        'username_claim' => 'name', // default name, option specifying claim containing username
        'header_name' => 'X-Access-Token', // default null, option for usage normal oauth2 header
        'token_prefix' => 'Bearer',
    ]
];


$app['security.firewalls'] = [
    'login' => [
        'pattern' => 'login|register',
        'anonymous' => true
    ],
    'public' => [
        'pattern' => "^{$app['eqt.api.prefix']}/timer$",
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

$app['security.role_hierarchy'] = [
    'ROLE_ADMIN' => [ 'ROLE_MEMBER' ],
    'ROLE_MEMBER' => [ 'ROLE_USER' ],
];
$app['security.access_rules'] = [
    ["^{$app['eqt.api.prefix']}/timer-group", 'ROLE_MEMBER'],
    ["^{$app['eqt.api.prefix']}/user", 'ROLE_ADMIN'],
];


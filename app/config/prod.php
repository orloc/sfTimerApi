<?php

// configure your app for the production environment

$app['eqt.entity_class_path'] = 'EQT\Api\Entity';

$app['security.jwt'] = [
    'secret_key' => 'Very_secret_key',
    'life_time'  => 86400,
    'options'    => [
        'username_claim' => 'name', // default name, option specifying claim containing username
        'header_name' => 'X-Access-Token', // default null, option for usage normal oauth2 header
        'token_prefix' => 'Bearer',
    ]
];

$app['twig.path'] = array(__DIR__.'/../../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../../var/cache/twig');

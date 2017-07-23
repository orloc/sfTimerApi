<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

// include the prod configuration
require __DIR__ . '/prod.php';

$app['db.options'] = [
    'driver'    => 'pdo_mysql',
    'host'      => 'localhost',
    'dbname'    => 'eq_timers',
    'user'      => 'root',
    'password'  => ''
];
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../../var/logs/silex_test.log',
    'monolog.level' => 'DEBUG',
    'monolog.name' => 'eqt'
));


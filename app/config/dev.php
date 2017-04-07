<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

// include the prod configuration
require __DIR__ . '/prod.php';

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../../var/logs/silex_dev.log',
    'monolog.level' => 'INFO',
    'monolog.name' => 'eqt'
));


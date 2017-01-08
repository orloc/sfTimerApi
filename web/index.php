<?php

require_once __DIR__.'/../app/bootstrap.php';

ini_set('display_errors', 0);

$app = require __DIR__ . '/../app/app.php';
$app->run();

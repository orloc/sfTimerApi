<?php

$prefix = $app['eqt.api.prefix'];

$authController = new \EQT\Api\Controller\SecurityController($app);

$controllers = [
    "timer" => new \EQT\Api\Controller\TimerController($app),
    "timer-group" => new \EQT\Api\Controller\TimerGroupController($app),
    'user' => new \EQT\Api\Controller\UserController($app)
];

$factory = $authController->connect($app);
$app->mount('', $factory);

foreach ($controllers as $k => $c) {
    $factory = $c->connect($app);
    $app->mount("{$prefix}", $factory);
}




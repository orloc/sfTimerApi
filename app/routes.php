<?php
$prefix = "/api/v1";

$authController = new \EQT\Api\Controller\SecurityController($app);

$controllers = [
    "timer" => new \EQT\Api\Controller\TimerController($app),
    "timer-group" => new \EQT\Api\Controller\TimerGroupController($app)
];

$factory = $authController->connect($app);
$app->mount('', $factory);

foreach ($controllers as $k => $c) {
    $factory = $c->connect($app);
    $app->mount("{$prefix}/{$k}", $factory);
}




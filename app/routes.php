<?php

$prefix = $app['eqt.api.prefix'];

$authController = new \EQT\Api\Controller\SecurityController($app);
$defaultController = new \EQT\Api\Controller\DefaultController($app);

$controllers = [
    "timer" => new \EQT\Api\Controller\TimerController($app),
    "timer-group" => new \EQT\Api\Controller\TimerGroupController($app),
    'user' => new \EQT\Api\Controller\UserController($app),
    'invitation' => new \EQT\Api\Controller\GroupInvitationController($app)
];

$auth = $authController->connect($app);
$default = $defaultController->connect($app);
$app->mount('', $auth);
$app->mount('', $default);

foreach ($controllers as $k => $c) {
    $factory = $c->connect($app);
    $app->mount("{$prefix}", $factory);
}


$app["cors-enabled"]($app);

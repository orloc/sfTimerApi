<?php
/**
 * Routing Definitions
 */

$controllers = [
    "timer" => new \EQT\Api\Controller\TimerController($app),
    "timer-group" => new \EQT\Api\Controller\TimerGroupController($app)
];

$prefix = "/api/v1";

foreach ($controllers as $k => $c) {
    $factory = $c->connect($app);
    $app->mount("{$prefix}/{$k}", $factory);
}

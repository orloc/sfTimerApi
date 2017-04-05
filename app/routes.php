<?php
/**
 * Routing Definitions
 */

$controller = new \EQT\Api\Controller\TimerController($app);

$factory = $controller->connect($app);

$app->mount('', $factory);
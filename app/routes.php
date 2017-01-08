<?php
/**
 * Routing Definitions
 */

$controller = new \EQT\Controller\TimerController($app);

$factory = $controller->connect($app);

$app->mount('', $factory);
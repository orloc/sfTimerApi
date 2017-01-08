<?php
ini_set('error_log', __DIR__.'/../var/logs/php_error.log');


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use EQT\Utility;

$app = new Application();

require __DIR__.'/providers.php';
require __DIR__ .'/config/dev.php';
require __DIR__.'/middleware.php';
require __DIR__.'/routes.php';

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return $app->json(Utility::formatError($e->getMessage(), $code));
});

return $app;

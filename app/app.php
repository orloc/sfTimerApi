<?php

use Silex\Application;

$app = new Application();

require __DIR__.'/providers.php';
require __DIR__.'/middleware.php';
require __DIR__.'/routes.php';

return $app;

<?php

// configure your app for the production environment

$app['eqt.entity_class_path'] = 'EQT\Api\Entity';

$app['twig.path'] = array(__DIR__.'/../../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../../var/cache/twig');

<?php

$app['eqt.models.user'] = function()  use ($app) { 
    return new \EQT\Api\Entity\User($app['security.encoder_factory']);
};
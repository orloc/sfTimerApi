<?php

$app['eqt.jwt_authenticator'] = function ($app) {
    return new EQT\Api\Security\JwtAuthenticator($app['security.encoder_factory']);
};

$app['eqt.models.user'] = function()  use ($app) { 
    return new \EQT\Api\Entity\User($app['security.encoder_factory']);
};
<?php

$app['eqt.jwt_authenticator'] = function ($app) {
    return new EQT\Api\Security\JWTAuthenticator($app['security.jwt'], $app['eqt.jwt_encoder']);
};

$app['eqt.jwt_encoder'] = function ($app) {
    $jwt = $app['security.jwt'];
    return new EQT\Api\Security\JWTEncoder($jwt['secret_key'], $jwt['life_time'], $jwt['algorithm']);
};

$app['eqt.models.user'] = function()  use ($app) { 
    return new \EQT\Api\Entity\User($app['security.encoder_factory']);
};
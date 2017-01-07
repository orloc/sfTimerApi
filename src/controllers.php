<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    return new JsonResponse(Utility::formatError('Hi grant'));
})
->bind('homepage');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return new JsonResponse(Utility::formatError($e->getMessage(), $code));
});

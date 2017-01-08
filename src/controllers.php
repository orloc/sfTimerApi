<?php

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

use EQT\Entity\Timer;


//Request::setTrustedProxies(array('127.0.0.1'));


// end middleware

$app->get(Utility::formatRoute('timer'), function(Request $request) use ($app) {
    // get all the timers
    // eventually get by active user group
});

$app->post(Utility::formatRoute('timer'), function(Request $request) use ($app) {
    $timer = Utility::mapRequest($request->request->all(), new Timer());
    
    var_dump($timer);
    // get request body data
    // validate data
    // save
});

$app->get(Utility::formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {
    // find timer
});

$app->patch(Utility::formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {
    
    // get request body data
    // validate data
    // save
});

$app->delete(Utility::formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {

});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return $app->json(Utility::formatError($e->getMessage(), $code));
});


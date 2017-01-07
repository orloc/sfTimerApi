<?php

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

//Request::setTrustedProxies(array('127.0.0.1'));

// Middleware

$app->before(function(Request $request, Application $app){
    if(in_array($request->getMethod(), ['POST', 'PATCH'])){
        if(!strlen($request->getContent())) {
            $app->abort(400, 'Empty Request body');
        }

        if ($request->getContentType() !== 'json'){
            $app->abort(415, 'Unsupported content type');
        }
        
        $body = $request->getContent();
        
        try {
            $json = json_decode($body, true);
            $request->request->replace(is_array($json) ? $json : []);
        } catch (\Exception $e){
            $app->abort(400, $e->getMessage());
        }
    }
});

// end middleware

$app->get(formatRoute('timer'), function(Request $request) use ($app) {

    // get all the timers
    // eventually get by active user group
});

$app->get(formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {
    // find timer
});

$app->post(formatRoute('timer'), function(Request $request) use ($app) {
    var_dump($request->request->all());
    // get request body data
    // validate data
    // save
});

$app->patch(formatRoute('timer'), function(Request $request) use ($app) {
    // get request body data
    // validate data
    // save
});

$app->delete(formatRoute('timer'), function(Request $request) use ($app) {

});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return $app->json(Utility::formatError($e->getMessage(), $code));
});

function formatRoute($path){
    return "/api/v1/{$path}";
}

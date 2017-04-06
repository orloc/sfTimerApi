<?php

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

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


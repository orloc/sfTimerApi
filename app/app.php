<?php
ini_set('error_log', __DIR__.'/../var/logs/php_error.log');

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EQT\Api\Utility;

$app = new Application();

require __DIR__.'/providers.php'; // needs to come first so we don't over-ride our config
require __DIR__.'/config/dev.php';
require __DIR__.'/services.php';

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

require __DIR__.'/routes.php';


$app->finish(function(Request $request, Response $response) use ($app) {
    $positiveCode = $response->getStatusCode() < 300;
    $privilegedRoute = !in_array($request->get('_route'), [
        'POST_login'
    ]);
    $validMethod = in_array($request->getMethod(), ['POST', 'PATCH', 'DELETE']);
    
    if( $positiveCode && $privilegedRoute && $validMethod) {
        $app['monolog']->info(sprintf("Sending message '%s' to zmq", $response->getContent()));

        $sockId = 'myId';
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, $sockId);
        $socket->connect("tcp://localhost:5555");
        $socket->send($response->getContent());
    }
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return Utility::JsonResponse(Utility::formatError($e->getMessage(), $code), $code, true);
});

$app['cors-enabled']($app);

return $app;

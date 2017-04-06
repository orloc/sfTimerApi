<?php
ini_set('error_log', __DIR__.'/../var/logs/php_error.log');


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EQT\Api\Utility;

$app = new Application();

require __DIR__.'/providers.php';
require __DIR__ .'/config/dev.php';
require __DIR__.'/middleware.php';
require __DIR__.'/routes.php';

$app['eqt.jwt_authenticator'] = function ($app) {
    return new EQT\Api\Security\JwtAuthenticator($app['security.encoder_factory']);
};

$app->after($app["cors"]);

$app->finish(function(Request $request, Response $response) use ($app) {
    if(in_array($request->getMethod(), ['POST', 'PATCH', 'DELETE']) && $response->getStatusCode() < 300) {
        $app['monolog']->info(sprintf("Sending message '%s' to zmq", $response->getContent()));

        $sockId = 'myId';
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, $sockId);
        $socket->connect("tcp://localhost:5555");
        $socket->send($response->getContent());
    }
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    
    var_dump($e->getMessage(), $code);
    die;
    return Utility::JsonResponse(Utility::formatError($e->getMessage(), $code), $code, true);
});

return $app;

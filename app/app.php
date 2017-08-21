<?php
ini_set('error_log', __DIR__.'/../var/logs/php_error.log');

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use EQT\Api\Utility;
use \EQT\MessageBuffer\ZMQConnect;

$app = new Application();

require __DIR__.'/providers.php'; // needs to come first so we don't over-ride our config
if (isset($_ENV['TEST_ENV']) && $_ENV['TEST_ENV'] === true ){
    require __DIR__.'/config/test.php';
} else {
    require __DIR__.'/config/dev.php';
}
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

$app->after(function(Request $request, Response $response){
    if($response->getStatusCode() === Response::HTTP_FORBIDDEN){
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);   
    }
    return $response;
    
});

$app->finish(function(Request $request, Response $response) use ($app) {
    if($_ENV['TEST_ENV'] === true) {
       return; 
    }
    
    if(ZMQConnect::isRequestValid($request, $response)) {
        $app['monolog']->info(sprintf("Sending message '%s' to zmq from %s", 
            $response->getContent(), 
            $request->get('_route')
        ));
        
        $user = $app['eqt.jwt_authenticator']->getCredentials($request);
        $socket = ZMQConnect::getSocket();
        try {
            $socket->send(ZMQConnect::package($user, $request, $response));
        } catch (\Exception $e){
            $app['monolog']->info(sprintf('Error while packaging message %s', $e->getMessage()));
        }
    }
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return Utility::JsonResponse(Utility::formatError($e->getMessage(), $code), $code, true);
});

$app['cors-enabled']($app);

return $app;

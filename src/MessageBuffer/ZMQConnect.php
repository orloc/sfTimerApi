<?php


namespace EQT\MessageBuffer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ZMQConnect {
    
    public static function isRequestValid(Request $request, Response $response) {
        $positiveCode = $response->getStatusCode() < 300;
        $privilegedRoute = !in_array($request->get('_route'), [
            'POST_login',
            'POST_register'
        ]);
        $validMethod = in_array($request->getMethod(), ['POST', 'PATCH', 'DELETE']);

        return $positiveCode && $privilegedRoute && $validMethod;
    }
    
    public static function getSocket(){

        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH);
        $socket->connect("tcp://127.0.0.1:5555");
        
        return $socket;
    }
    
    public static function package($user, Request $request, Response $response) {
        $message = [
            'route' => $request->get('_route'),
            'method' => $request->getMethod(),
            'acting_user' => json_encode($user),
            'body' => json_decode($response->getContent(), true)
        ];
        
        return json_encode($message);
    
    }
}

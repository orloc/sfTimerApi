<?php

namespace EQT\MessageBuffer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ZMQConnect {
    
    const CREATE = 'action:create';
    const UPDATE = 'action:update';
    const DELETE = 'action:delete';
    
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
        $resource = explode('_',$request->get('_route'));
        $action = self::getAction(array_shift($resource));
        $entity = false;
        while(!$entity){
            $next = array_shift($resource);
            
            if ($next === null) break;
            
            if (in_array($next, ['timergroup', 'timer', 'invitation '])){
                $entity = $next;
            }
        }
        
        if (!$entity){
            // log some shit but dont continue
            throw new \Exception('Unable to find valid entity map');
        }

        return json_encode([
            'entity' => $entity,
            'action' => $action,
            'acting_user' => json_encode($user),
            'message_sent' => new \DateTime(),
            'content' => json_decode($response->getContent(), true)
        ]);
    }
    
    private static function getAction($method){
       switch($method){
           case 'POST': return self::CREATE;
           case 'PATCH': return self::UPDATE;
           case 'DELETE': return self::DELETE;
           default:
               throw new \Exception('Invalid action or method resolution');
       } 
    }
}

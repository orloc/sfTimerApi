<?php
namespace EQT\Api\Controller;

use EQT\Api\Utility;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('/ping', [$this,'ping']);
        return $controllers;
    }
    
    public function ping(Request $request){
        return Utility::JsonResponse([ 'now' => new \DateTime() ]);
    }
}

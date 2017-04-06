<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class TimerController extends AbstractCRUDController implements ControllerProviderInterface {
    
    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('/', [$this, 'all']);
        $controllers->get('/{id}', [$this, 'getBy']);

        $controllers->post('', [$this,'create']);
        $controllers->patch('/{id}', [$this, 'update']);
        $controllers->delete('/{id}', [$this, 'delete']);

        return $controllers;
    }
}

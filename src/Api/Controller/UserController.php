<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractCRUDController implements ControllerProviderInterface {
    
    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('', [$this, 'all']);
        $controllers->get('/{id}', [$this, 'getBy']);

        $controllers->post('', [$this,'create']);
        $controllers->patch('/{id}', [$this, 'update']);
        $controllers->delete('/{id}', [$this, 'delete']);

        return $controllers;
    }
    
    public function create(Request $request) {
        return parent::create($request); 
    }
    
    public function update(Request $request, $id) {
        return parent::update($request, $id); 
    }
}

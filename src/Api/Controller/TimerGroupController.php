<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class TimerGroupController extends AbstractCRUDController implements ControllerProviderInterface {

    protected $jwtAuthenticator;
    
    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('', [$this, 'all']);
        $controllers->get('/{id}', [$this, 'getBy']);

        $controllers->post('', [$this,'create']);
        $controllers->patch('/{id}', [$this, 'update']);
        $controllers->delete('/{id}', [$this, 'delete']);

        $this->jwtAuthenticator = $app['eqt.jwt_authenticator'];

        return $controllers;
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();
        
        $request->request->replace(array_merge($content, [ 'created_by' => $user['id']]));
        
        return parent::create($request);
    }

}

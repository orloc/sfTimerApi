<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupInvitationController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/invitation', [$this, 'getInvitationsByUser']);
        $controllers->post('/invitation', [$this,'create']);
        $controllers->patch('/invitation/{id}', [$this,'update']);

        return $controllers;
    }

    public function getInvitationsByUser(Request $request, $timerGroup){
        $user = $this->jwtAuthenticator->getCredentials($request);
        // check user access to group 
        $filters = [ 'timer_group_id' => $timerGroup ];
        return parent::all($request, $filters);
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();
        $body = array_merge($content, [ 'created_by' => $user['id']]); 
        $request->request->replace($body);

        return parent::create($request);
    }
}

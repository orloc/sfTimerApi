<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\AbstractEntity;
use EQT\Api\Entity\TimerGroup;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TimerController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/timer-group/{timerGroup}/timer', [$this, 'getTimersByGroup']);
        $controllers->get('/timer-group/{timerGroup}/timer/{id}', [$this, 'getBy']);
        $controllers->post('/timer-group/{timerGroup}/timer', [$this,'create']);

        return $controllers;
    }

    public function getTimersByGroup(Request $request, $timerGroup){
        $user = $this->jwtAuthenticator->getCredentials($request);
        
        // check user access to group 
        
        $filters = [ 'timer_group_id' => $timerGroup ];
        return parent::all($request, $filters);

    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();

        $request->request->replace(array_merge($content, [ 'created_by' => $user['id']]));

        return parent::create($request);
    }
    
    public function beforeCreate(AbstractEntity $entity) {
        if (!TimerGroup::hasItem($this->db, $entity->getTimerGroupId())) {
            $this->app->abort(Response::HTTP_NOT_FOUND, 'Invalid timer group');
        }
    }
}

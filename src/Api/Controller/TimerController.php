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
        $controllers->post('/timer-group/{timerGroup}/timer', [$this,'create']);
        $controllers->patch('/timer-group/{timerGroup}/timer/{id}', [$this,'update']);
        $controllers->delete('/timer-group/{timerGroup}/timer/{id}', [$this,'deleteBy']);

        return $controllers;
    }

    public function getTimersByGroup(Request $request, $timerGroup){
        $user = $this->jwtAuthenticator->getCredentials($request);
        // check user access to group 
        $filters = [ 'timer_group_id' => $timerGroup, 'deleted_at' => null];
        return parent::all($request, $filters);

    }
    
    public function deleteBy(Request $request, $timerGroup, $id) {
        // check that we are allowed to delete this
        $user = $this->jwtAuthenticator->getCredentials($request);
        
        $exists = TimerGroup::hasItem($this->db, ['id' => $timerGroup, 'deleted_at' => null]);
        if (!$exists){
            $this->app->abort(Response::HTTP_NOT_FOUND, 'Invalid timer group id');
        }
        
        $groupOwnsTimer = TimerGroup::isTimerOwner($this->db, $timerGroup, $id);
        
        if (intval($groupOwnsTimer['exists']) === 0){
            $this->app->abort(Response::HTTP_NOT_FOUND, 'Timer not owned by group');
        }
        
        return parent::delete($request, $id); // TODO: Change the autogenerated stub
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();
        $body = array_merge($content, [ 'created_by' => $user['id']]); 
        $request->request->replace($body);

        return parent::create($request);
    }
    
    public function beforeCreate(AbstractEntity $entity) {
        if (!TimerGroup::hasItem($this->db, [ 'id' => intval($entity->getTimerGroupId()), 'deleted_at' => null])) {
            $this->app->abort(Response::HTTP_NOT_FOUND, 'Invalid timer group');
        }
    }
}

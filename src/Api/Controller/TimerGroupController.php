<?php
namespace EQT\Api\Controller;

use Doctrine\DBAL\Connection;
use EQT\Api\Entity\Timer;
use EQT\Api\Entity\TimerGroup;
use EQT\Api\Utility;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TimerGroupController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('/timergroup', [$this, 'all']);
        $controllers->get('/timergroup/member', [$this, 'getTimerGroupMembers']);

        $controllers->post('/timergroup', [$this,'create']);
        $controllers->patch('/timergroup/{id}', [$this,'update']);
        $controllers->delete('/timergroup/{id}', [$this, 'delete']);

        return $controllers;
    }
    
    public function all(Request $request, $imposedFilters = []) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        return Utility::JsonResponse(TimerGroup::getAllTimerGroupsByUser($this->db, $user));
    }

    public function beforeDelete($id){
        $existingTimers = Timer::all($this->db, ['timer_group_id' => $id, 'deleted_at' => null]);
        TimerGroup::deleteAssociatedTimers($this->db, $existingTimers);
    }
    
    public function afterDelete($id) {
        TimerGroup::deleteAssociatedUsers($this->db, $id);
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();
        
        $request->request->replace(array_merge($content, [ 'created_by' => $user['id']]));
        
        return parent::create($request);
    }
    
    public function getTimerGroupMembers(Request $request){
        $group_id = $request->query->get('group_id');
        $user = $this->jwtAuthenticator->getCredentials($request);
        
        if (!$group_id){
            $this->app->abort(Response::HTTP_BAD_REQUEST, 'Invalid query param');
        }
        
        if (!TimerGroup::isGroupMember($this->db, $user['id'], $group_id)){
            $this->app->abort(Response::HTTP_UNAUTHORIZED, 'Not a member of this group');
        }
        
        $members = TimerGroup::getGroupMembers($this->db, $group_id, $user['id']);

        return Utility::JsonResponse(array_map(function($d) {
            if ($d['approved'] === 'true'){
                $d['approved'] = boolval($d['approved']);
            } else {
                $d['approved'] = intval($d['approved']);
            }
            return $d;
        }, $members), Response::HTTP_OK);
    }

}

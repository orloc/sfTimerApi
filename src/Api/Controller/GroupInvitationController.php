<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\GroupInvitation;
use EQT\Api\Entity\User;
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
        $filters = [ 
            'timer_group_id' => $timerGroup,
        ];
        return parent::all($request, $filters);
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();

        if (array_diff_key($content, array_flip(['group_id', 'profile_name']))){
            $this->app->abort(400, 'Post body mismatch');
        }
        
        if (!isset($content['profile_name']) || !User::hasItem($this->db, ['profile_name' => $content['profile_name'], 'deleted_at' => null])){
            $this->app->abort(404, 'User not found');
        }

        if ($user['profile_name'] === $content['profile_name']){
            $this->app->abort(400, 'You cannot invite yourself');
        }
        
        $invitee = User::getBy($this->db, ['profile_name' => $content['profile_name']]);

        $entityMap = [
            'group_id' => $content['group_id'],
            'invitee_id' => $invitee['id'],
            'inviter_id' => $user['id'],
        ];

        $hasInvitation = GroupInvitation::hasItem($this->db, array_merge($entityMap, ['accepted' => null, 'deleted_at' => null]));
        if ($hasInvitation){
            $this->app->abort(409, 'User already has a pending invitation');
        }

        $request->request->replace($entityMap);

        return parent::create($request);
    }
}

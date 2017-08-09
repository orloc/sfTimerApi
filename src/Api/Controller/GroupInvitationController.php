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
        
        $controllers->post('/invitation', [$this,'create']);
        $controllers->patch('/invitation/{id}', [$this,'update']);

        return $controllers;
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();
        
        if (!isset($content['profile_name']) || !User::hasItem($this->db, ['profile_name' => $content['profile_name']])){
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
            'permission_grant' => $content['permission_grant']
        ];
        
        $hasInvitation = GroupInvitation::hasItem($this->db, array_merge($entityMap, ['accepted' => null]));
        if ($hasInvitation){
            $this->app->abort(409, 'User already has a pending invitation');
        }

        $request->request->replace($entityMap);

        return parent::create($request);
    }
}

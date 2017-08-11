<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\AbstractEntity;
use EQT\Api\Entity\GroupInvitation;
use EQT\Api\Entity\TimerGroup;
use EQT\Api\Entity\User;
use EQT\Api\Utility;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupInvitationController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->post('/invitation', [$this,'create']);
        $controllers->get('/invitation', [$this,'getMyInvitations']);
        $controllers->get('/invitation/check', [$this,'hasPendingInvitations']);
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
        
        // check if user is already member of group

        $entityMap = [
            'group_id' => $content['group_id'],
            'invitee_id' => $invitee['id'],
            'inviter_id' => $user['id'],
            'status' => GroupInvitation::STATUS_NEW,
            'permission_grant' => $content['permission_grant']
        ];
        
        $hasInvitation = GroupInvitation::hasItem($this->db, $entityMap);
        if ($hasInvitation){
            $this->app->abort(409, 'User already has a pending invitation');
        }

        $request->request->replace($entityMap);

        return parent::create($request);
    }
    
    public function hasPendingInvitations(Request $request){
        $user = $this->jwtAuthenticator->getCredentials($request);
        $invitations = GroupInvitation::hasItem($this->db, [
            'invitee_id' => $user['id'],
            'status' => GroupInvitation::STATUS_NEW
        ]);
        
        return Utility::JsonResponse([ 'has_invitations' => $invitations]);
    }
    
    public function getMyInvitations(Request $request){
        $user = $this->jwtAuthenticator->getCredentials($request);
        $invitations = GroupInvitation::all($this->db, [
            'invitee_id' => $user['id']
        ]);

        $inviterIds = array_map(function($inv){
            return intval($inv['inviter_id']);
        }, $invitations);

        $groupIds = array_map(function($inv){
            return intval($inv['group_id']);
        }, $invitations);
        
        $users = array_map(function($u) {
            return Utility::mapRequest($u, $this->app['eqt.models.user'])->serialize();
        }, User::getIn($this->db, $inviterIds));
        
        $groups = array_map(function($g) {
            return Utility::mapRequest($g, new TimerGroup())->serialize();
        }, TimerGroup::getIn($this->db, $groupIds));
        
        $userRef = [];
        $groupRef = [];

        foreach ($groups as $item){
            $groupRef[intval($item['id'])] = $item;
        }
        foreach ($users as $item){
            $userRef[intval($item['id'])] = $item;
        }
        
        $response = array_map(function($inv) use (&$userRef, &$groupRef){
            return array_merge($inv, [
                'user' => $userRef[$inv['inviter_id']],
                'group' => $groupRef[$inv['group_id']]
            ]) ;
        }, $invitations);

        return Utility::JsonResponse($response);
    }
    
    public function beforeUpdate(AbstractEntity $entity){
        $time = new \DateTime();
        $entity->setActionedAt($time->format('Y-m-d H:i:s'));
    }
    
    public function afterUpdate(AbstractEntity $entity) {
        $invite = Utility::mapRequest(GroupInvitation::getBy($this->db, ['id' => $entity->getId()]), new GroupInvitation());

        if ($invite->getStatus() === GroupInvitation::STATUS_APPROVED){
            TimerGroup::addMember($this->db, $invite); 
        }
    }

    public function update(Request $request, $id, $constraints = false){
        $user = $this->jwtAuthenticator->getCredentials($request);

        // can update?
        if (!GroupInvitation::hasItem($this->db, [
            'id' => $id,
            'invitee_id' => $user['id'],
            'status' => GroupInvitation::STATUS_NEW
        ])){
            $this->app->abort(409, 'Already updated');
        }

        return parent::update($request, $id, GroupInvitation::getUpdateConstraints());
    }
}

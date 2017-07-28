<?php
namespace EQT\Api\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints as Assert;

class GroupInvitation extends AbstractEntity {
    
    protected $inviter_id;

    protected $invitee_id;
    
    protected $group_id;

    protected $accepted;
    
    protected $permission_grant;
    
    protected $accepted_at;

    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('inviter_id',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('invitee_id',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('permission_grant',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('group_id',[new Assert\NotBlank()]);
    }

    public function beforeSave(Connection $db) {
        $this->created_at = new \DateTime();
    }
    
    public function afterSave(AbstractEntity $entity, Connection $db) {
        return false;
    }

    public function beforeUpdate(Connection $db){
        return false;
    }
    
    function afterUpdate(Array $data, Connection $db){
        return false;
    }

    public function getUpdateFields(){
        return ['accepted'];
    }

    public function setInviterId($id){
        $this->inviter_id = $id;
        return $this;
    }
    
    public function getInviterId(){
        return $this->invitee_id;
    }
    
    public function getPermissionGrant(){
        return $this->permission_grant;
    }
    
    public function setPermissionGrant($grant){
        $this->permission_grant = $grant;
        return $this;
    }
    
    public function setInviteeId($id){
        $this->invitee_id = $id;
        return $this;
    }
    
    public function getInviteeId(){
        return $this->invitee_id;
    }
    
    public function setGroupId($id){
        $this->group_id = $id;
        return $this;
    }
    
    public function getGroupId(){
        return $this->group_id;
    }
    
    public function setAccepted($accepted){
        $this->accepted = $accepted;
        return $this;
    }
    
    public function getAccepted(){
        return $this->accepted;
    }
    
    public function setAcceptedAt($acceptedAt){
        $this->accepted_at = $acceptedAt;
        return $this;
    }
    
    public function getAcceptedAt(){
        return $this->accepted_at;
    }
}
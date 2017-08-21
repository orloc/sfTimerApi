<?php
namespace EQT\Api\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints as Assert;

class GroupInvitation extends AbstractEntity {
    
    const STATUS_NEW = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    
    protected $inviter_id;

    protected $invitee_id;
    
    protected $group_id;

    protected $permission_grant;
    
    protected $actioned_at;
    
    protected $status;

    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('inviter_id',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('invitee_id',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('permission_grant',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('group_id',[new Assert\NotBlank()]);
    }

    static public function getUpdateConstraints(){
        return new Assert\Collection([
            'status' => new Assert\Choice(array_flip(self::getStatuses())),
            'id' => new Assert\NotBlank()
        ]);
    }
    
    static public function getStatuses(){
        return [
            'NEW' => self::STATUS_NEW,
            'APPROVED' => self::STATUS_APPROVED,
            'REJECTED' => self::STATUS_REJECTED
        ];
    }
    
    public static function resolveStatus($status, $numToString = false){
        $statuses = [
            self::STATUS_NEW => 'NEW',
            self::STATUS_APPROVED => 'APPROVED',
            self::STATUS_REJECTED => 'REJECTED'
        ];
        
        if ($numToString){
            $statuses = array_flip($statuses);
            $status = strtoupper($status);
        } 
        
        if (!isset($statuses[$status])){
            return -1;
        }

        return $statuses[$status];
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
        return ['status', 'actioned_at'];
    }

    public function setInviterId($id){
        $this->inviter_id = $id;
        return $this;
    }
    
    public function getInviterId(){
        return $this->inviter_id;
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
    
    public function getActionedAt(){
        return $this->actioned_at;
    }
    
    public function setActionedAt($actioned){
        $this->actioned_at = $actioned;
        return $this;
    }
    
    public function getStatus(){
        return intval($this->status);
    }
    
    public function setStatus($status){
        if (is_string($status)){
            if (strtoupper($status) === 'NEW'){
                $status = self::STATUS_NEW; 
            }
            if (strtoupper($status) === 'APPROVED'){
                $status = self::STATUS_APPROVED; 
            }
            if (strtoupper($status) === 'REJECTED'){
                $status = self::STATUS_REJECTED; 
            }
        }
        
        $this->status = $status;
        return $this;
    }
}
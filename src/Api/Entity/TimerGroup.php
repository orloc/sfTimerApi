<?php
namespace EQT\Api\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class TimerGroup extends AbstractEntity {

    const PRIVILEGE_OWNER = 'OWNER';
    const PRIVILEGE_READ = 'READ';
    const PRIVILEGE_WRITE = 'WRITE';

    public static $join_table = 'users_timer_groups';

    public static $transact_on_create = true;

    protected $name;

    protected $description;

    protected $created_by;

    protected $deleted_at;
    
    public function getUpdateFields(){
        return ['name', 'description'];
    }

    public function beforeSave(Connection $db) {
        return false;
    }

    public function beforeUpdate(Connection $db){
        return false;
    }

    function afterUpdate(Array $data, Connection $db){
        return false;
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('name', [new Assert\NotBlank()]);
    }
    
    public function afterSave(AbstractEntity $data, Connection $db) {
        $this->createJoinRecord($db, $data);
    }

    public function getDeletedAt() {
        return $this->deleted_at;
    }

    public function getName() {
        return $this->name;
    }
    
    public function setName($name){
        $this->name = $name;
        return $this;
    }
    
    public function getDescription() {
        return $this->description;
    }
     
    public function setDescription($description) {
        $this->description = $description;     
        return $this;
    }
    
    public function getCreatedBy(){
        return $this->created_by;
    }
    
    public function setCreatedBY($created_by){
        $this->created_by = $created_by;
        return $this;
    }
    
    public function setDeletedAt($time){
        $this->deleted_at = $time;
    }
    
    public static function getPrivileges(){
        return [
            'PRIVILEGE_OWNER' => self::PRIVILEGE_OWNER,
            'PRIVILEGE_READ' => self::PRIVILEGE_READ,
            'PRIVILEGE_WRITE' => self::PRIVILEGE_WRITE
        ];
    }

    public static function getAllTimerGroupsByUser(Connection $db, $user){
        $table = self::resolveTableName();
        $join = self::$join_table;
        $query = "select tg.* from {$table} tg
                  join {$join} utg on tg.id = utg.timer_group_id 
                  where utg.user_id = ? 
                  and utg.deleted_at is NULL
                  and tg.deleted_at is NULL";
        
        return $db->fetchAll($query, [ $user['id']]);
    }
    
    public static function isTimerOwner(Connection $db, $groupId, $timerId){
        $table = self::resolveTableName();
        $query = "select count(t.id) as 'exists'
                  from {$table} tg
                  join timers t on t.timer_group_id = tg.id
                  where tg.id = ?
                  and t.id = ?
        ";
        
        return $db->fetchAssoc($query, [ $groupId, $timerId]);
    }

    public static function getGroupMembers(Connection $db, $groupId, $me = null){
        $groupId = intval($groupId);
        $query = "select u.profile_name, 
                  u.id,
                  gi.permission_grant as 'privilege', 
                  gi.actioned_at as 'created',
                  gi.status as 'approved'
                  from users u
                  join group_invitations gi on u.id = gi.invitee_id
                  where gi.group_id = ?";

        if ($me){
            $query .= " and u.id != ?";
        }

        $query .= " order by approved";
        
        $params =  $me 
            ?  [ intval($groupId), intval($me)]
            :  [ $groupId ];
        
        return $db->fetchAll($query, $params);
    }

    public static function isGroupMember(Connection $db, $user_id, $group_id){
        $exists = $db->executeQuery("
              select count(*) as c 
              from users_timer_groups 
              where timer_group_id = ? and user_id = ?", 
            [$group_id, $user_id]);
        
        return boolval($exists->fetchAll()[0]['c']);
    }
    
    public static function deleteAssociatedTimers(Connection $db, array $existingTimers){
        $ids = array_map(function($timer){
            return intval($timer['id']);
        }, $existingTimers);
        
        $query = "update timers set deleted_at=NOW() where id in (?)";
        $db->executeQuery($query, [$ids], [Connection::PARAM_INT_ARRAY]);
        
    }
    
    public static function deleteAssociatedUsers(Connection $db, $timer_id){
        $db->executeQuery("update users_timer_groups set deleted_at=NOW() where timer_group_id = ?", [$timer_id]);
    }
    
    public static function addMember(Connection $db, GroupInvitation $invitation) {
        $data = [
            'user_id' => intval($invitation->getInviteeId()),
            'timer_group_id' => intval($invitation->getGroupId()),
            'created_at' => new \DateTime(),
            'user_privilege' => $invitation->getPermissionGrant()
        ];
        
        try {
            $db->insert(self::$join_table, $data, [ 'created_at' => 'datetime'] );
        } catch (ConstraintViolationException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }

    private function createJoinRecord(Connection $db, $data){
        $rowData = [
            'user_id' => $data->getCreatedBy(),
            'timer_group_id' => $data->getId(),
            'created_at' => new \DateTime(),
            'user_privilege' => self::PRIVILEGE_OWNER
        ];

        try {
            $db->insert(self::$join_table, $rowData, [ 'created_at' => 'datetime'] );
        } catch (ConstraintViolationException $e) {
            $db->rollBack();
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }
}
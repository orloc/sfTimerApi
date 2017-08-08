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
    
    public static function getPrivileges(){
        return [
            'PRIVILEGE_OWNER' => self::PRIVILEGE_OWNER,
            'PRIVILEGE_READ' => self::PRIVILEGE_READ,
            'PRIVILEGE_WRITE' => self::PRIVILEGE_WRITE
        ];
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
        $query = "select data.* from (
                    select u.profile_name, 
                           u.id,
                           utg.user_privilege as 'privilege', 
                           true as 'approved'
                    from users u
                    join users_timer_groups utg on u.id=utg.user_id
                    where utg.timer_group_id = ?
                    UNION 
                    select u.profile_name, 
                           u.id,
                           gi.permission_grant as 'privilege', 
                           false as 'approved'
                    from users u
                    join group_invitations gi on u.id = gi.invitee_id
                    where gi.group_id = ?
                  ) as data ";

        if ($me){
            $query .= " where data.id != ?";
        }

        $query .= " order by data.approved";
        
        $params =  $me 
            ?  [ $groupId, intval($groupId), intval($me)] 
            :  [ $groupId, $groupId ];
        
        return $db->fetchAll($query, $params);
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
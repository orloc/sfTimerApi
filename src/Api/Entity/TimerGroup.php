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
    
    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('name', [new Assert\NotBlank()]);
    }
    
    public function afterSave(AbstractEntity $data, Connection $db) {
        $this->createJoinRecord($db, $data);
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
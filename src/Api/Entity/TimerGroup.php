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

    const JOIN_TABLE = 'users_timer_groups';

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

    private function createJoinRecord(Connection $db, $data){
        $rowData = [
            'user_id' => $data->getCreatedBy(),
            'timer_group_id' => $data->getId(),
            'created_at' => new \DateTime(),
            'user_privilege' => self::PRIVILEGE_OWNER
        ];

        try {
            $db->insert(self::JOIN_TABLE, $rowData, [ 'created_at' => 'datetime'] );
        } catch (ConstraintViolationException $e) {
            $db->rollBack();
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }
}
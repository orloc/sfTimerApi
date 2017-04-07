<?php

namespace EQT\Api\Entity;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Connection;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractEntity {

    protected $id;

    protected $created_at;

    protected $deleted_at;

    public function __construct(){
        $this->created_at = new \DateTime();
    }
    
    public function serialize()
    {
        $reflect = new \ReflectionClass(get_class($this));
        $accessor = PropertyAccess::createPropertyAccessor();
        
        $ret = [];
        
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PROTECTED) as $p){
            $ret[$p->getName()] = $accessor->getValue($this, $p->getName());

            if ($ret[$p->getName()] instanceof \DateTime) {
                $ret[$p->getName()] = $ret[$p->getName()]->format(\DateTime::ISO8601);
            }
        }
        
        return $ret;
    }
    
    public function setId($id) {
        $this->id = $id; 
        return $this;
    }

    public function getId(){
        return $this->id;
    }

    public function getCreatedAt(){
        return $this->created_at;
    }

    public function getDeletedAt() {
        return $this->deleted_at;
    }

    public function save(Connection $db){
        $data = get_object_vars($this);
        unset($data['id']);
        $db->insert($this->resolveTableName(), $data, [ 'created_at' => 'datetime'] );
        $id =  $db->lastInsertId();
        $this->setId($id);
    }

    public function update(Connection $db){
        $data = get_object_vars($this);
        $id = $data['id'];
        
        unset($data['id']);
        unset($data['created_at']);

        $db->update($this->resolveTableName(), $data, [ 'id' => $id]);
    }

    public static function all(Connection $db, $filter = [], $order = []){
        $table = self::resolveTableName();
        $query = "select * from {$table}";
        return $db->fetchAll($query);
    }
    
    public static function getBy(Connection $db, $id){
        $table = self::resolveTableName();
        $query = "select * from {$table} 
                  where id  = ? and deleted_at is null 
                  limit 1";
        
        return $db->fetchAssoc($query, [ $id ]);
    }

    public static function hasItem(Connection $db, $id) {
        $table = self::resolveTableName();
        $query = "select count(*) as count from {$table} 
                  where id  = ? and deleted_at is null 
                  limit 1";

        return $db->fetchAssoc($query, [ $id ])['count'] > 0;
    }

    public static function delete(Connection $db, $id){
        return $db->update(self::resolveTableName(), [ 'deleted_at' => new \DateTime() ], [ 'id' => $id ], [
            'datetime'
        ]);
    }

    protected static function resolveTableName() {
        $reflect = new \ReflectionClass(static::class);
        return Inflector::tableize(
            Inflector::pluralize($reflect->getShortName())
        );
    }
}
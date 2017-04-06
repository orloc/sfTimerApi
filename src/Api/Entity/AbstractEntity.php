<?php

namespace EQT\Api\Entity;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Connection;

abstract class AbstractEntity {

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
    
    public function create(){
        
    }
    
    public function update(){
        
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
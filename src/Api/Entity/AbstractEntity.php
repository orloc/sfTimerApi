<?php

namespace EQT\Api\Entity;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractEntity {

    protected $id;

    protected $created_at;

    protected $deleted_at;
    
    public static $serialization_black_list = [];
    
    public static $transact_on_create = false;

    public static $join_table = null;
    
    abstract public function getUpdateFields();

    abstract public function beforeSave(Connection $db);
    abstract public function afterSave(AbstractEntity $entity, Connection $db);

    abstract public function beforeUpdate(Connection $db);
    abstract function afterUpdate(Array $data, Connection $db);
    
    public function serialize()
    {
        $reflect = new \ReflectionClass(get_class($this));
        $accessor = PropertyAccess::createPropertyAccessor();
        
        $ret = [];
        
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PROTECTED) as $p){
            if (in_array($p->getName(), static::$serialization_black_list)){
                continue;
            }
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
    
    public function setCreatedAt($time){
        if (!$time instanceof \DateTime) {
            $time = new \DateTime($time);
        }
        $this->created_at = $time;
        return $this;
    }

    public function getCreatedAt(){
        return $this->created_at;
    }

    public function getDeletedAt() {
        return $this->deleted_at;
    }

    public function save(Connection $db){
        $this->beforeSave($db);
        
        $this->setCreatedAt(new  \DateTime());
        $data = get_object_vars($this);
        unset($data['serialization_black_list']);
        unset($data['id']);
        
        if (static::$transact_on_create) {
            $db->beginTransaction();
        }

        try {
            $db->insert($this->resolveTableName(), $data, [ 'created_at' => 'datetime'] );
        } catch (ConstraintViolationException $e) {
            if (static::$transact_on_create) {
                $db->rollBack();
            }
            throw new ConflictHttpException($e->getMessage(), $e);
        }
        $id =  $db->lastInsertId();
        $this->setId($id);
        
        $this->afterSave($this, $db);
        
        if (static::$transact_on_create){
            $db->commit();
        }
    }

    public function update(Connection $db){
        $data = get_object_vars($this);
        $columns = array_flip($this->getUpdateFields());
        $id = $data['id'];
        
        if (count($columns)){
            $fields = array_filter($data, function($d, $k) use($columns){
                return isset($columns[$k]);
            }, ARRAY_FILTER_USE_BOTH);
        } else {
            $fields = $data;  
        }
        
        try {
            $db->update($this->resolveTableName(), $fields, [ 'id' => $id]);
        } catch (ConstraintViolationException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }
    }

    public static function all(Connection $db, $filtersArr = [], $order = []){
        $table = self::resolveTableName();
        
        $suggestedFilters = array_merge($filtersArr, ['deleted_at' => null]);
        list($filters, $values) = self::buildWhere($suggestedFilters);

        $query = "select {$table}.* from {$table} where {$filters}";

        return $db->fetchAll($query, $values);
    }

    public static function getBy(Connection $db, $filtersArr = []){
        $table = self::resolveTableName();

        $suggestedFilters = array_merge($filtersArr, ['deleted_at' => null]);
        list($filters, $values) = self::buildWhere($suggestedFilters);

        $query = "select * from {$table} where {$filters} limit 1";

        return $db->fetchAssoc($query, $values);
    }

    public static function hasItem(Connection $db, $filtersArr = []) {
        $table = self::resolveTableName();
        $suggestedFilters = array_merge($filtersArr, ['deleted_at' => null]);
        
        list($filters, $values) = self::buildWhere($suggestedFilters);
        $query = "select count(*) as 'count' 
                  from {$table} 
                  where {$filters} limit 1";
        
        return $db->fetchAssoc($query, $values)['count'] > 0;
    }

    public static function delete(Connection $db, $id){
        return $db->update(self::resolveTableName(), [ 'deleted_at' => new \DateTime() ], [ 'id' => $id ], [
            'datetime'
        ]);
    }

    protected static function buildWhere($filters = []){
        $values = [];
        $query  = join(" AND ", array_map(function($val, $key) use (&$values) {
            if ($val === null){
                return "{$key} IS null";
            }
            array_push($values, $val);
            return "{$key} = ?";
        }, $filters, array_keys($filters)));

        return [$query, $values];
    }


    protected static function resolveTableName() {
        $reflect = new \ReflectionClass(static::class);
        return Inflector::tableize(
            Inflector::pluralize($reflect->getShortName())
        );
    }
}


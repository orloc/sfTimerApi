<?php

namespace EQT\Api\Controller;

use EQT\Api\Entity\AbstractEntity;
use EQT\Api\Utility;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCRUDController {

    protected $app;
    protected $db;
    protected $jwtAuthenticator;
    
    public function __construct(Application $app){
        $this->app = $app;
        $this->db = $app['db'];
        $this->jwtAuthenticator = $app['eqt.jwt_authenticator'];
    }
    
    public function beforeCreate(AbstractEntity $entity){}
    public function beforeUpdate(AbstractEntity $entity){}
    public function beforeDelete($id){}

    public function afterCreate(AbstractEntity $entity){}
    public function afterUpdate(AbstractEntity $entity){}
    public function afterDelete($id){}

    public function all(Request $request, $filters = []){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        $objects = array_map(function($i) use ($class) {
            return Utility::mapRequest($i, is_object($class) ? $class : new $class())->serialize();
        }, $className::all($this->db, $filters));
        
        return Utility::JsonResponse($objects, Response::HTTP_OK);
    }

    public function getBy(Request $request, $id){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        $entity = $className::getBy($this->db, [ 'id' => $id ]);

        if (!$entity){
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$class} {$id} not found");
        }

        $data = Utility::mapRequest($entity, is_object($class) ? $class : new $class())->serialize();

        return Utility::JsonResponse($data, Response::HTTP_OK);
    }

    public function create(Request $request){
        $class = $this->getEntityClass();
        
        
        $entity = $this->validateInput($request->request->all(), 
            is_object($class) ? $class : new $class()
        );
        
        $this->beforeCreate($entity);
        
        $entity->save($this->db);

        $this->afterCreate($entity);

        return Utility::JsonResponse($entity->serialize(), Response::HTTP_CREATED);
    }

    public function update(Request $request, $id){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        if (!$className::hasItem($this->db, ['id' => $id])) {
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$className} {$id} not found");
        }

        $entity = $this->validateInput(array_merge($request->request->all(), [ 'id' => $id]), 
            is_object($class) ? $class : new $class()
        );
        
        $this->beforeUpdate($entity);

        $entity->update($this->db);

        $this->afterUpdate($entity);

        return Utility::JsonResponse($entity->serialize(), Response::HTTP_OK);
    }

    public function delete(Request $request, $id){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        if (!$className::hasItem($this->db, [ 'id' => $id])) {
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$className} {$id} not found");
        }

        $this->beforeDelete($id);
        $className::delete($this->db, $id);
        $this->afterDelete($id);

        return Utility::JsonResponse([ 'id' => $id ], Response::HTTP_OK);
    }

    protected function validateInput(Array $body, $object){
        $object = Utility::mapRequest($body, $object);
        $errors = Utility::handleValidationErrors($this->app['validator']->validate($object));

        if ($errors) {
            $this->app->abort(Response::HTTP_BAD_REQUEST, $errors);
        }

        return $object;
    }

    private function getEntityClass(){
        $reflect = new \ReflectionClass($this);
        $shortName = str_replace('Controller', '', $reflect->getShortName());

        $entityPath = "eqt.models.{$shortName}";

        $entityService = isset($this->app[strtolower($entityPath)])
            ? $this->app[strtolower($entityPath)]
            : false;

        if ($entityService){
            return $entityService;
        }

        return join('\\', [
            $this->app['eqt.entity_class_path'],
            $shortName
        ]);
    }
}
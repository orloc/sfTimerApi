<?php

namespace EQT\Api\Controller;

use EQT\Api\Utility;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCRUDController {

    protected $app;
    protected $db;
    
    public function __construct(Application $app){
        $this->app = $app;
        $this->db = $app['db'];
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

    public function all(Request $request){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        return Utility::JsonResponse($className::all($this->db), Response::HTTP_OK);
    }

    public function getBy(Request $request, $id){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        $entity = $className::getBy($this->db, $id);

        if (!$entity){
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$class} {$id} not found");
        }

        return Utility::JsonResponse($entity, Response::HTTP_OK);
    }

    public function create(Request $request){
        $class = $this->getEntityClass();
        
        $entity = $this->validateInput($request->request->all(), 
            is_object($class) ? $class : new $class()
        );
        
        $entity->save($this->db);

        return Utility::JsonResponse($entity->serialize(), Response::HTTP_CREATED);
    }

    public function update(Request $request, $id){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        if (!$className::hasItem($this->db, $id)) {
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$className} {$id} not found");
        }

        $entity = $this->validateInput(array_merge($request->request->all(), [ 'id' => $id]), 
            is_object($class) ? $class : new $class()
        );

        $entity->update($this->db);

        return Utility::JsonResponse($entity->serialize(), Response::HTTP_OK);
    }

    public function delete(Request $request, $id){
        $class = $this->getEntityClass();
        $className = is_object($class) ? get_class($class) : $class;
        
        if (!$className::hasItem($this->db, $id)) {
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$className} {$id} not found");
        }

        $className::delete($this->db, $id);

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
}
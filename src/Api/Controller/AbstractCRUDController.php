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
        return join('\\', [
            $this->app['eqt.entity_class_path'],
            str_replace('Controller', '', $reflect->getShortName())
        ]);
    }

    public function all(Request $request){
        $class = $this->getEntityClass();
        return Utility::JsonResponse($class::all($this->db), Response::HTTP_OK);
    }

    public function getBy(Request $request, $id){
        $class = $this->getEntityClass();
        $entity = $class::getBy($this->db, $id);

        if (!$entity){
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$class} {$id} not found");
        }

        return Utility::JsonResponse($entity, Response::HTTP_OK);
    }

    public function create(Request $request){
        $class = $this->getEntityClass();
        
        $entity = $this->validateInput($request->request->all(), new $class());
        $entity->save($this->db);

        return Utility::JsonResponse($entity->__toString(), Response::HTTP_CREATED);
    }

    public function update(Request $request, $id){
        $class = $this->getEntityClass();
        
        if (!$class::hasItem($this->db, $id)) {
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$class} {$id} not found");
        }

        $entity = $this->validateInput(array_merge($request->request->all(), [ 'id' => $id])
            , new $class());

        $entity->update($this->db);

        return Utility::JsonResponse($entity->__toString(), Response::HTTP_OK);
    }

    public function delete(Request $request, $id){
        $class = $this->getEntityClass();
        if (!$class::hasItem($this->db, $id)) {
            $this->app->abort(Response::HTTP_NOT_FOUND, "{$class} {$id} not found");
        }

        $class::delete($this->db, $id);

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
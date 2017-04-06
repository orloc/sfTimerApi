<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\Timer;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use EQT\Api\Utility;
/**
 * Class MainController
 * @package Webview\Controller
 */
class TimerController implements ControllerProviderInterface {
    protected $app;
    protected $db;

    public function __construct(Application $app){
        $this->app = $app;
        $this->db = $app['db'];
    }
    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get(Utility::formatRoute('timer'), [$this, 'get']);
        $controllers->get(Utility::formatRoute('timer/{id}'), [$this, 'getBy']);

        $controllers->post(Utility::formatRoute('timer'), [$this,'create']);
        $controllers->patch(Utility::formatRoute('timer/{id}'), [$this, 'update']);
        $controllers->delete(Utility::formatRoute('timer/{id}'), [$this, 'delete']);

        return $controllers;
    }

    public function get(Request $request){
        $timers = array_map(function($timeJson) {
            return $this->app['serializer']->deserialize($timeJson, Timer::class, 'json');
        }, Timer::all($this->db));

        $json = $this->app['serializer']->serialize($timers, 'json');

        return Utility::JsonResponse($json, 200);
    }

    public function getBy(Request $request, $id){
        $timer = Timer::getBy($this->db, $id);
        
        if (!$timer){
            $this->app->abort(404, "Timer {$id} not found");
        }

        return Utility::JsonResponse($timer, 200);
    }

    public function create(Request $request){
        $this->validateInput($request);

    }

    public function update(Request $request, $id){
        $timer = $this->findKeyFromId($id);
        if (!$timer){
            $this->app->abort(404, "Timer {$id} not found");
        }

        $json = $this->doUpdate($request);

        return Utility::JsonResponse($json, 200);
    }

    public function delete(Request $request, $id){
        if (!Timer::hasItem($this->db, $id)) {
            $this->app->abort(404, "Timer {$id} not found");
        }

        Timer::delete($this->db, $id);

        return Utility::JsonResponse([ 'id' => $id ], 200);
    }

    protected function validateInput(Request $request){
        $object = Utility::mapRequest($request->request->all(), new Timer());
        $errors = Utility::handleValidationErrors($this->app['validator']->validate($object));

        if ($errors) {
            $this->app->abort(400, $errors);
        }
    }


    protected function doUpdate(Request $request, $create = false){

        if ($create){
            if ($this->redis->hexists(Timer::$redisKey, $object->getLabel())){
                $this->app->abort(409, 'Duplicate timer in set');
            }
        }

        $res = $this->redis->hset(Timer::$redisKey, $object->getLabel(), $json);

        if (!$res){
            $this->app->abort(500, 'Unable to add item to redis index');
        }

        return $json;
    }


    protected function findKeyFromId($id) {
        $parsedId = str_replace('_', ' ', $id);
        return $this->redis->hget(Timer::$redisKey, $parsedId);
    }
}

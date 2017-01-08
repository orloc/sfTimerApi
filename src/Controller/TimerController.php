<?php
namespace EQT\Controller;

use EQT\Entity\Timer;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use EQT\Utility;
/**
 * Class MainController
 * @package Webview\Controller
 */
class TimerController implements ControllerProviderInterface {
    protected $app;
    protected $redis;
    
    public function __construct(Application $app){
        $this->app = $app;
        $this->redis = $app['predis'];
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
        $controllers->delete(Utility::formatRoute('timer/{id}'), [$this, 'remove']);
        
        return $controllers;
    }
    
    public function get(Request $request){
        $timers = array_values(array_map(function($timeJson) {
            return $this->app['serializer']->deserialize($timeJson, 'EQT\Entity\Timer', 'json');
        }, $this->redis->hgetall(Timer::$redisKey)));

        $json = $this->app['serializer']->serialize($timers, 'json');

        return Utility::JsonResponse($json, 200);
    }

    public function getBy(Request $request, $id){
        $parsedId = str_replace('_', ' ', $id);
        $timer = $this->redis->hget(Timer::$redisKey, $parsedId);
        
        if (!$timer){
            $this->app->abort(404, "Timer {$id} not found");
        }
        
        return Utility::JsonResponse($timer, 200);
    }
    
    public function create(Request $request){
        $object = Utility::mapRequest($request->request->all(), new Timer());        
        $errors = Utility::handleValidationErrors($this->app['validator']->validate($object));

        if ($errors) {
            $this->app->abort(400, $errors);
        }

        $json = $this->app['serializer']->serialize($object, 'json');
        
        if ($this->redis->hexists(Timer::$redisKey, $object->getLabel())){
            $this->app->abort(403, 'Duplicate timer in set');
        }
        
        $res = $this->redis->hset(Timer::$redisKey, $object->getLabel(), $json);
        
        if (!$res){
           $this->app->abort(500, 'Unable to add item to redis index'); 
        }
        
        return Utility::JsonResponse($json, 200);
    }
    
    public function update(Request $request){
        
    }
    
    public function delete(Request $request, $id){
        $parsedId = str_replace('_', ' ', $id);
        $timer = $this->redis->hget(Timer::$redisKey, $parsedId);
        if (!$timer){
            $this->app->abort(404, "Timer {$id} not found");
        }
        
        if (!$this->redis->hdel(Timer::$redisKey, $parsedId)) {
            $this->app->abort(500, "Unable to delete {$id}");
        }
        return Utility::JsonResponse('', 200);
    }
}
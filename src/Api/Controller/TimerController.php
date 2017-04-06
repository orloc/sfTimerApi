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
        return Utility::JsonResponse(Timer::all($this->db), 200);
    }

    public function getBy(Request $request, $id){
        $timer = Timer::getBy($this->db, $id);
        
        if (!$timer){
            $this->app->abort(404, "Timer {$id} not found");
        }

        return Utility::JsonResponse($timer, 200);
    }

    public function create(Request $request){
        $timer = $this->validateInput($request->request->all(), new Timer());
        
        $timer->save($this->db);

        return Utility::JsonResponse($timer->__toString(), 200);

    }

    public function update(Request $request, $id){
        if (!Timer::hasItem($this->db, $id)) {
            $this->app->abort(404, "Timer {$id} not found");
        }
        
        $timer = $this->validateInput(array_merge($request->request->all(), [ 'id' => $id])
            , new Timer());

        $timer->update($this->db);

        return Utility::JsonResponse($timer->__toString(), 200);
    }

    public function delete(Request $request, $id){
        if (!Timer::hasItem($this->db, $id)) {
            $this->app->abort(404, "Timer {$id} not found");
        }

        Timer::delete($this->db, $id);

        return Utility::JsonResponse([ 'id' => $id ], 200);
    }

    protected function validateInput(Array $body, $object){
        $object = Utility::mapRequest($body, $object);
        $errors = Utility::handleValidationErrors($this->app['validator']->validate($object));

        if ($errors) {
            $this->app->abort(400, $errors);
        }

        return $object;
    }
}

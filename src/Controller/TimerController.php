<?php
namespace EQT\Controller;

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
    
    public function __construct(Application $app){
        $this->app = $app;
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
        
    }
    
    public function getBy(Request $request){
        
    }
    
    public function create(Request $request){
        
    }
    
    public function update(Request $request){
        
    }
    
    public function delete(Request $request){
        
    }
}
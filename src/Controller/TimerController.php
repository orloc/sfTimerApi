<?php
namespace EQT\Controller;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Utility;
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
        $controllers->get(Utility::formatRoute('timer'), function(Request $request) use ($app) {
            // get all the timers
            // eventually get by active user group
        });

        $app->post(Utility::formatRoute('timer'), function(Request $request) use ($app) {
            $timer = Utility::mapRequest($request->request->all(), new Timer());

            var_dump($timer);
            // get request body data
            // validate data
            // save
        });

        $app->get(Utility::formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {
            // find timer
        });

        $app->patch(Utility::formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {

            // get request body data
            // validate data
            // save
        });

        $app->delete(Utility::formatRoute('timer/{id}'), function(Request $request, $id) use ($app) {

        });
        return $controllers;
    }
    /**
     * Default page action
     * @return mixed
     */
    public function defaultAction(){
        return $this->app['twig']->render('page.html.twig');
    }
    /**
     * Handles the search action // does the crawling
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAction(Request $request){
        $search =  $request->request->get('search', null);
        $errors = $this->app['validator']->validate($search, new Assert\Url());
        if (count($errors) > 0){
            // we know there can only be one invalid field
            $e = array_shift($errors->getIterator()->getArrayCopy());
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }
        $scraper = $this->app['webview.scraper']();
        try {
            $data = $scraper->scrapePage($search);
            return new JsonResponse($data);
        } catch (\Exception $e){
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
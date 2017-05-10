<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\AbstractEntity;
use EQT\Api\Entity\TimerGroup;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TimerController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('', [$this, 'all']);
        $controllers->get('/{id}', [$this, 'getBy']);

        $controllers->post('', [$this,'create']);
        $controllers->patch('/{id}', [$this, 'update']);
        $controllers->delete('/{id}', [$this, 'delete']);


        return $controllers;
    }

    public function create(Request $request) {
        $user = $this->jwtAuthenticator->getCredentials($request);
        $content = $request->request->all();

        $request->request->replace(array_merge($content, [ 'created_by' => $user['id']]));

        return parent::create($request);
    }
    
    public function beforeCreate(AbstractEntity $entity) {
        if (!TimerGroup::hasItem($this->db, $entity->getTimerGroupId())) {
            $this->app->abort(Response::HTTP_NOT_FOUND, 'Invalid timer group');
        }
    }
}

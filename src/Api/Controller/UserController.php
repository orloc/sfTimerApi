<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class UserController extends AbstractCRUDController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        /*
        $controllers->get('user', [$this, 'all']);
        $controllers->get('user/{id}', [$this, 'getBy']);

        $controllers->post('user', [$this, 'create']);
        $controllers->delete('user/{id}', [$this, 'delete']);
        */

        return $controllers;
    }
}

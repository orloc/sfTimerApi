<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class TimerGroupController extends AbstractCRUDController implements ControllerProviderInterface {
    
    use Application\SecurityTrait;
    
    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('', [$this, 'all'])->secure('ROLE_MEMBER');
        $controllers->get('/{id}', [$this, 'getBy'])->secure('ROLE_MEMBER');

        $controllers->post('', [$this,'create'])->secure('ROLE_MEMBER');
        $controllers->patch('/{id}', [$this, 'update'])->secure('ROLE_MEMBER');
        $controllers->delete('/{id}', [$this, 'delete'])->secure('ROLE_MEMBER');

        return $controllers;
    }

}

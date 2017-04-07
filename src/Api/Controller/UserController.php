<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractCRUDController implements ControllerProviderInterface {
    
    use Application\SecurityTrait;
    
    public function connect(Application $app){
        $controllers = $app['controllers_factory'];
        $controllers->get('', [$this, 'all'])->secure('ROLE_ADMIN');
        $controllers->get('/{id}', [$this, 'getBy'])->secure('ROLE_ADMIN');

        $controllers->post('', [$this,'create'])->secure('ROLE_ADMIN');
        $controllers->patch('/{id}', [$this, 'update'])->secure('ROLE_ADMIN');
        $controllers->delete('/{id}', [$this, 'delete'])->secure('ROLE_ADMIN');

        return $controllers;
    }
    
    public function create(Request $request) {
        return parent::create($request); 
    }
    
    public function update(Request $request, $id) {
        return parent::update($request, $id); 
    }
}

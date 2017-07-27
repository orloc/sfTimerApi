<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\User;
use EQT\Api\Utility;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractCRUDController implements ControllerProviderInterface {

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        $controllers->get('user/me', [$this, 'getMe']);

        /*
        $controllers->get('user/{id}', [$this, 'getBy']);

        $controllers->post('user', [$this, 'create']);
        $controllers->delete('user/{id}', [$this, 'delete']);
        */

        return $controllers;
    }
    
    public function getMe(Request $request){
        $userToken = $this->jwtAuthenticator->getCredentials($request);
        
        if (!User::hasItem($this->db, [ 'id' => $userToken['id']])){
            $this->app->abort(404, 'User not found'); 
        }
        
        $user = User::getBy($this->db, ['id' => $userToken['id']]);
        $bList = array_flip(User::$serialization_black_list);
        
        $filtered = array_filter($user, function($v, $k) use ($bList) {
            return !isset($bList[$k]);
        }, ARRAY_FILTER_USE_BOTH);
        
        return Utility::JsonResponse($filtered, Response::HTTP_OK);
    }
}

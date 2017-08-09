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
        $controllers->patch('user', [$this, 'userUpdate']);

        return $controllers;
    }
    
    public function getMe(Request $request){
        $userToken = $this->jwtAuthenticator->getCredentials($request);
        
        if (!User::hasItem($this->db, [ 'id' => $userToken['id'], 'deleted_at' => null])){
            $this->app->abort(404, 'User not found'); 
        }

        $user = User::getBy($this->db, ['id' => $userToken['id'], 'deleted_at' => null]);
        $data = Utility::mapRequest($user, $this->app['eqt.models.user'])->serialize();
        
        return Utility::JsonResponse($data, Response::HTTP_OK);
    }
    
    public function userUpdate(Request $request){
        $userToken = $this->jwtAuthenticator->getCredentials($request);
        $body = $request->request->all();
        
        if (!isset($body['id']) || $userToken['id'] !== $body['id']){
            $this->app->abort(Response::HTTP_FORBIDDEN);
        }
        
        return parent::update($request, $userToken['id'], User::getUpdateConstraints());
    }
}

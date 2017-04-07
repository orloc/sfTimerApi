<?php
namespace EQT\Api\Controller;

use EQT\Api\Utility;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController implements ControllerProviderInterface {
    
    protected $app;
    protected $user_provider;
    
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];
        $controllers->post('/login', [$this, 'login']);

        return $controllers;
    }
    
    public function __construct(Application $app) {
        $this->app = $app;
        $this->user_provider = new \EQT\Api\Security\UserProvider($app['db'], $app['eqt.models.user']);
    }

    public function login(Request $request) {

        $data = $request->request->all();
        
        if (empty($data['username']) || empty($data['password'])){
            $this->app->abort(Response::HTTP_BAD_REQUEST, sprintf("Unable to process request - bad fields"));
        }

        $user = $this->user_provider->loadUserByUsername($data['username']);
        
        if (!$user || !$this->app['security.encoder.bcrypt']->isPasswordValid($user->getPassword(), $data['password'], '')) {
            $this->app->abort(Response::HTTP_NOT_FOUND, sprintf('Username "%s" does not exist or the password is invalid', $data['username']));
        }  
        
        $response = [
            'token' => $this->app['security.jwt.encoder']->encode(['name' => $user->getUsername()]),
        ];

        return Utility::JsonResponse($response, Response::HTTP_OK);
    }
}

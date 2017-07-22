<?php
namespace EQT\Api\Controller;

use EQT\Api\Entity\User;
use EQT\Api\Utility;
use EQT\Api\Security\Core\JWTEncoder;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SecurityController implements ControllerProviderInterface {
    
    protected $app;
    protected $encoder;
    protected $user_provider;
    protected $user_manager; 
    
    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];
        $controllers->post('/login', [$this, 'login']);
        $controllers->post('/register', [$this, 'register']);

        return $controllers;
    }
    
    public function __construct(Application $app) {
        $jwt = $app['security.jwt'];
        $this->user_manager = $app['eqt.managers.user'];
        $this->app = $app;
        $this->user_provider = new \EQT\Api\Security\UserProvider($app['db'], $app['eqt.models.user']);
        $this->encoder = new JWTEncoder($jwt['secret_key'], $jwt['life_time'], $jwt['algorithm'] );
    }

    public function login(Request $request) {

        $data = $request->request->all();
        
        
        if (!isset($data['username']) || !isset($data['password'])){
            $this->app->abort(Response::HTTP_BAD_REQUEST, "Unable to process request - bad fields");
        }

        try {
            $user = $this->user_provider->loadUserByUsername($data['username']);
            var_dump($user);
            die;
            
            if( !$this->app['security.encoder.bcrypt']->isPasswordValid($user->getPassword(), $data['password'], '')) {
                $this->app->abort(Response::HTTP_UNAUTHORIZED, 'Invalid password');
            }

            return Utility::JsonResponse($this->packageToken($this->encoder->encode($user)), Response::HTTP_OK);
            
        } catch (UsernameNotFoundException $e){
            $this->app->abort(Response::HTTP_NOT_FOUND, sprintf('Username "%s" does not exist', $data['username']));
        } 
    }
    
    public function register(Request $request) {

        $data = $request->request->all();

        if (!isset($data['username']) || !isset($data['password'])){
            $this->app->abort(Response::HTTP_BAD_REQUEST, "Unable to process request - bad fields");
        }
        
        if (User::hasItem($this->app['db'], $data['username'], 'username')) { 
            $this->app->abort(Response::HTTP_CONFLICT, sprintf("Username %s already exists", $data['username']));
        }
        
        $newUser = $this->user_manager->createUser($data['username'], $data['password']);
        
        try {
            $newUser->save($this->app['db']);
            return Utility::JsonResponse($this->packageToken($this->encoder->encode($newUser)), Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->app->abort(500, $e->getMessage());
        }
    }
    
    protected function packageToken($token){
        return [
            'token' => $token,
            'requested' => new \DateTime()
        ];
    }
}

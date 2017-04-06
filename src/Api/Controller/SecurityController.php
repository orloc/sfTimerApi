<?php
namespace EQT\Api\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
        $this->user_provider = new \EQT\Api\Security\UserProvider($app['db']);
    }

    public function login(Request $request) {
        $data = $request->request->all();
        
        if (empty($data['username']) || empty($data['password'])){
            throw new UsernameNotFoundException(sprintf("Unable to process request - bad fields"));
        }

        $user = $this->user_provider->loadUserByUsername($data['username']);
        if (!$this->app['security.encoder.digest']->isPasswordValid($user->getPassword(), $data['password'], '')) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $data['username']));
        } 
        
        $response = [
            'success' => true,
            'token' => $app['security.jwt.encoder']->encode(['name' => $user->getUsername()]),
        ];
    }
}

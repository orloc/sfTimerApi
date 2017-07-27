<?php


namespace EQT\Api\Manager;


use EQT\Api\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager { 
    
    protected $encoder;
    
    public function __construct(EncoderFactoryInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function createUser($username, $password, $profileName, $role = 'ROLE_MEMBER'){
        $user = new User($this->encoder);
        
        $user->setUsername($username)
             ->setProfileName($profileName)
             ->setType(User::TYPE_REGISTERED)
             ->setRoles($role)
             ->setCreatedAt(new \DateTime())
             ->setPlainPassword($password);
        
        return $user;
    }
}
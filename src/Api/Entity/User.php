<?php
namespace EQT\Api\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class User extends AbstractEntity implements UserInterface {
    
    protected $username;

    protected $email;

    protected $password;
    
    protected $roles; 
    
    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('username',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('email',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('password',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('roles',[new Assert\NotBlank()]);
    }

    public function getUsername(){
        return $this->username;
    }
    
    public function setUsername($name) {
        $this->username = $name;
        return $this;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    public function setEmail($email){
        $this->email = $email; 
        return $this;
    }
    
    public function getPassword(){
        return $this->password;
    }
    
    public function setPassword($pass) {
        $this->password = $pass;
        return $this;
    }
    
    public function getRoles(){
        return $this->roles;
    }
    
    public function setRoles($roles){
        $this->roles = $roles;
        return $this;
    }
    
    // Bcrypt has no salt
    public function getSalt(){ return; }
    public function eraseCredentials(){}
}
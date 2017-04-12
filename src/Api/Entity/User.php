<?php
namespace EQT\Api\Entity;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class User extends AbstractEntity implements UserInterface {

    const TYPE_REGISTERED = 'REGISTERED';
    const TYPE_ANONYMOUS  = 'ANONYMOUS';

    private $plain_password;

    public static $serialization_black_list = [
        'password'
    ];
    
    protected $username;

    protected $email;

    protected $password;

    protected $type;
    
    protected $roles; 
    
    private $encoder_factory;


    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('username',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('type',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('plain_password',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('roles',[new Assert\NotBlank()]);
    }
    
    public function __construct(EncoderFactoryInterface $encoder){
        $this->encoder_factory = $encoder;
        
        parent::__construct();
    }
    
    public function beforeSave() {
        if (!$this->plain_password) {
            throw new \Exception('No password to serialize');
        }

        $this->updatePassword();
    }

    public function update(Connection $db) {
        if ($this->plain_password !== null) {
            $this->updatePassword();
        }
        
        parent::update($db);
    }
    
    public function setPlainPassword($password){
        $this->plain_password = $password;
        return $this;
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
        return [$this->roles];
    }
    
    public function setRoles($roles){
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials(){
        $this->plain_password = null;
    }

    protected function updatePassword(){
        $this->setPassword(
            $this->encoder_factory->getEncoder($this)
                ->encodePassword($this->plain_password, '')
        );
        
        $this->eraseCredentials();
    }
    
    // Bcrypt has no salt
    public function getSalt(){ return; }
}
<?php
namespace EQT\Api\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class TimerGroup extends AbstractEntity {
    
    protected $name;

    protected $description;

    protected $user_id; 
    
    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('name', [new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('user_id', [new Assert\NotBlank()]);
    }

    public function getName() {
        return $this->name;
    }
    
    public function setName($name){
        $this->name = $name;
        return $this;
    }
    
    public function getDescription() {
        return $this->description;
    }
     
    public function setDescription($description) {
        $this->description = $description;     
        return $this;
    }
    
    public function getUserId(){
        return $this->user_id;
    }

    public function setUserId($user_id){
        $this->user_id = $user_id;
        return $this;
    }
}
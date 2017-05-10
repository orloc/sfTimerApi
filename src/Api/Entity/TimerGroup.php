<?php
namespace EQT\Api\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class TimerGroup extends AbstractEntity {

    const PRIVILEGE_OWNER = 'OWNER';
    const PRIVILEGE_READ = 'READ';
    const PRIVILEGE_WRITE = 'WRITE';

    protected $name;

    protected $description;

    protected $created_by;
    
    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('name', [new Assert\NotBlank()]);
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
    
    public function getCreatedBy(){
        return $this->created_by;
    }
    
    public function setCreatedBY($created_by){
        $this->created_by = $created_by;
        return $this;
    }
}
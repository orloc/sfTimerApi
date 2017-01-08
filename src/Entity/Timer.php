<?php
namespace EQT\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Timer {
    
    public static $redisKey = 'eqt:entity:timer';

    protected $start_time;

    protected $duration;

    protected $reset_count;

    protected $label;
    
    protected $created_at;

    public function __construct(){
        $this->created_at = new \DateTime();
    }
    
    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('start_time', [new Assert\Time()]);
        $metadata->addPropertyConstraints('duration',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('reset_count',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('label',[new Assert\NotBlank()]);
    }
    
    public function getStartTime(){
        return $this->start_time;
    }
    
    public function setStartTime($time){
        $this->start_time = $time;
        return $this;
    }
    
    public function getDuration(){
        return $this->duration;
    }
    
    public function setDuration($duration){
        $this->duration = $duration; 
        return $this;
    }
    
    public function getResetCount(){ 
        return $this->reset_count;
    }
    
    public function setResetCount($count){
        $this->reset_count = $count;
        return $this;
    }
    
    public function getLabel(){
        return $this->label;
    }
    
    public function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    public function getCreatedAt(){
        return $this->created_at;
    }
}
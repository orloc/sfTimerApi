<?php
namespace EQT\Api\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Timer extends AbstractEntity {
    
    protected $duration;

    protected $last_tick;

    protected $label;
    
    protected $user_id; 
    
    protected $timer_group_id;
    
    static public function loadValidatorMetadata(ClassMetadata $metadata){
        $metadata->addPropertyConstraints('duration',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('label',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('user_id',[new Assert\NotBlank()]);
    }


    public function getDuration(){
        return $this->duration;
    }
    
    public function setDuration($duration){
        $this->duration = $duration; 
        return $this;
    }
    
    public function getLabel(){
        return $this->label;
    }
    
    public function setLabel($label){
        $this->label = $label;
        return $this;
    }

    public function getUserId(){
        return $this->user_id;
    }

    public function setUserId($user_id){
        $this->user_id = $user_id;
        return $this;
    }

    public function getTimerGroupId(){
        return $this->timer_group_id;
    }

    public function setTimerGroupId($timer_group_id){
        $this->timer_group_id = $timer_group_id;
        return $this;
    }
    
    public function getLastTick(){
        return $this->last_tick; 
    }
    
    public function setLastTick($lastTick){
        $this->last_tick = $lastTick;
        return $this;
    }
}
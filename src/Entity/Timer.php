<?php
namespace EQT\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class Timer {

    public $id;

    public $start_time;

    public $duration;

    public $reset_count;

    public $label;

    static public function loadValidatorMetadata(ClassMetadata $metadata){

        $metadata->addPropertyConstraints('start_time', [new Assert\Time()]);
        $metadata->addPropertyConstraints('duration',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('reset_count',[new Assert\NotBlank()]);
        $metadata->addPropertyConstraints('label',[new Assert\NotBlank()]);
    }

}
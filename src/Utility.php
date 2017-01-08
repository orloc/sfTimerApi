<?php
namespace EQT;
use Symfony\Component\PropertyAccess\PropertyAccess;


class Utility {
    
    public static function formatError($message, $status=400){
        return [
            'message' => $message,
            'status_code' => $status
        ];
    }

    public static function mapRequest(Array $data, $object){
        $accessor = PropertyAccess::createPropertyAccessor();
        
        foreach ($data as $k => $v) {
            if ($accessor->isWritable($object, $k)) {
                $accessor->setValue($object, $k, $v);
            }
        }
        
        return $object;
    }

    public static function formatRoute($path){
        return "/api/v1/{$path}";
    }
}
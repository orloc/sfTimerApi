<?php
namespace EQT\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintViolationList;

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
    
    public static function handleValidationErrors(ConstraintViolationList $errors ) {
        if (count($errors) > 0) {
            $arrErr = [];

            foreach ($errors as $e){
                array_push($arrErr, $e);
            }

            return join(' - ', array_map(function($err){
                return "{$err->getPropertyPath()}: {$err->getMessage()}";
            }, $arrErr));
        }
    }
    
    public static function JsonResponse($data, $code, $serialize = true) {
        if ($serialize) {
            try {
                $json = json_encode($data);
            } catch(\Exception $e) {
                $json = json_encode($e->getMessage());
                $code = 500;
            }
        }

        return new Response(isset($json) ? $json  : $data, $code, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Headers' => 'Content-Type',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}
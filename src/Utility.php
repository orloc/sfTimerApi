<?php


class Utility {
    
    public static function formatError($message, $status=400){
        return [
            'message' => $message,
            'status_code' => $status
        ];
    }
}
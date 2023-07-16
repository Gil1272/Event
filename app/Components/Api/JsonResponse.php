<?php
namespace App\Components\Api;

use Illuminate\Http\JsonResponse as LaravelJsonResponse;

class JsonResponse{

    public static function send(bool $error,?string $message, mixed $data = null,int $status=200){
        $returnValue = [];
        $returnValue["error"] = $error;
        if($message){
            $returnValue["message"] = $message;
        }
        if($data){
            $returnValue["data"] = $data;
        }
        return new LaravelJsonResponse($returnValue,$status);
    }
}

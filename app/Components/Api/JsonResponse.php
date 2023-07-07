<?php
namespace App\Components\Api;

use Illuminate\Http\JsonResponse as LaravelJsonResponse;

class JsonResponse{

    public static function send(bool $error,?string $message, mixed $data = null,int $status=200){
        return new LaravelJsonResponse([
            'error' => $error,
            'message' => $message,
            'data' => $data
        ],$status);
    }
}
<?php

use App\Http\Controllers\Auths\AuthController;
use App\Http\Middleware\Api\CorsConfig;
use App\Http\Middleware\Api\Jwt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix("auth")->middleware([CorsConfig::class])->group(function(){

    Route::post("login",[AuthController::class,"login"]);

    Route::post("register",[AuthController::class,"register"]);
});

Route::prefix("auth")->middleware([CorsConfig::class,Jwt::class])->group(function(){

    Route::get("logout",[AuthController::class,'logout']);

    Route::post("refresh",[AuthCOntroller::class,"refresh"]);

    Route::get("me",[AuthController::class, "me"]);
});

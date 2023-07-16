<?php

use App\Http\Controllers\Auths\AuthController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\Utils\UtilsController;
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

Route::prefix("node")->middleware([CorsConfig::class])->group(function(){
    Route::get("country",[UtilsController::class,'getCountries']);
    Route::get("event-status",[UtilsController::class,'getEventStatus']);
    Route::get("event-type",[UtilsController::class,'getEventType']);
});

Route::prefix("auth")->middleware([CorsConfig::class,Jwt::class])->group(function(){

    Route::get("logout",[AuthController::class,'logout']);

    Route::post("refresh",[AuthCOntroller::class,"refresh"]);

    Route::get("me",[AuthController::class, "me"]);
});

Route::prefix("event/")->middleware([CorsConfig::class,Jwt::class])->group(function(){

    Route::post("",[EventController::class,"store"]);
    Route::put("{id:id}",[EventController::class,"update"])->where(["id" => "[0-9]+"]);
    // Route::put("status/{id:id}/{state:state}",[EventController::class,"changeStatus"]);
    // Route::put("visibility/{id:id}/{state:state}",[EventController::class,"changeVisibility"]);
    // Route::post("clone/{id:id}",[EventController::class,"duplicate"]);
    Route::delete("{id:id}",[EventController::class,"destroy"])->withoutMiddleware([VerifyCsrfToken::class])->where(["id" => "[0-9]+"]);
});

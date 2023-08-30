<?php

use Illuminate\Http\Request;
use App\Http\Middleware\Api\Jwt;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Api\CorsConfig;
use App\Http\Controllers\Auths\AuthController;
use App\Http\Controllers\Utils\UtilsController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\Organizers\OrganizerController;
use App\Http\Controllers\Sponsors\SponsorController;
use App\Http\Controllers\Tickets\TicketController;

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

Route::get('/api.yaml', function () {
    return response(file_get_contents(public_path('api.yaml')))
        ->header('Content-Type', 'application/x-yaml');
});

Route::prefix("auth")->middleware([CorsConfig::class])->group(function(){

    Route::post("login",[AuthController::class,"login"]);

    Route::post("register",[AuthController::class,"register"]);
});

Route::prefix("node")->middleware([CorsConfig::class])->group(function(){
    Route::get("country",[UtilsController::class,'getCountries']);
    Route::get("event-status",[UtilsController::class,'getEventStatus']);
    Route::get("event-type",[UtilsController::class,'getEventType']);
    Route::get("ticket-type",[UtilsController::class,'getTicketType']);
});

Route::prefix("auth")->middleware([CorsConfig::class,Jwt::class])->group(function(){

    Route::get("logout",[AuthController::class,'logout']);

    Route::post("refresh",[AuthCOntroller::class,"refresh"]);

    Route::get("me",[AuthController::class, "me"]);
});

Route::prefix("event/")->middleware([CorsConfig::class,Jwt::class])->group(function(){

    Route::post("",[EventController::class,"store"]);
    Route::get("",[EventController::class,"getMyEvents"]);
    Route::get("{id:id}",[EventController::class,"show"])->where(["id" => "[a-z0-9]{24}"]);
    Route::post("{id:id}",[EventController::class,"update"])->where(["id" => "[a-z0-9]{24}"]);
    // Route::put("status/{id:id}/{state:state}",[EventController::class,"changeStatus"]);
    // Route::put("visibility/{id:id}/{state:state}",[EventController::class,"changeVisibility"]);
    Route::put("clone/{id:id}",[EventController::class,"duplicate"])->where(["id" => "[a-z0-9]{24}"]);
    Route::delete("{id:id}",[EventController::class,"destroy"])->withoutMiddleware([VerifyCsrfToken::class])->where(["id" => "[a-z0-9]{24}"]);
});

Route::prefix("ticket")->group(function(){
     Route::post("{id:id}",[TicketController::class,"update"])->where(["id" => "[a-z0-9]{24}"]);
     Route::delete("{id:id}",[TicketController::class,"destroy"])->withoutMiddleware([VerifyCsrfToken::class])->where(["id" => "[a-z0-9]{24}"]);
     Route::post("",[TicketController::class,"store"]);
    Route::get("{id:id}",[TicketController::class,"show"])->where(["id" => "[a-z0-9]{24}"]);
});

Route::prefix("organizer")->group(function(){
    Route::post("{id:id}",[OrganizerController::class,"update"])->where(["id" => "[a-z0-9]{24}"]);
    Route::delete("{id:id}",[OrganizerController::class,"destroy"]);
    Route::post("",[OrganizerController::class,"store"]);
   Route::get("{id:id}",[OrganizerController::class,"show"])->where(["id" => "[a-z0-9]{24}"]);
});

Route::prefix("sponsor")->group(function(){
    Route::post("{id:id}",[SponsorController::class,"update"])->where(["id" => "[a-z0-9]{24}"]);
    Route::delete("{id:id}",[SponsorController::class,"destroy"]);
    Route::get("{id_event:id_event}",[SponsorController::class,"getEventAllSponsors"])->where(["id_event" => "[a-z0-9]{24}"]);
    Route::post("",[SponsorController::class,"store"]);
   Route::get("specificsponsor/{id:id}",[SponsorController::class,"show"])->where(["id" => "[a-z0-9]{24}"]);
});

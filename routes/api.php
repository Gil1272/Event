<?php

use Illuminate\Http\Request;
use App\Http\Middleware\Api\Jwt;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\Api\CorsConfig;
use App\Http\Controllers\Auths\AuthController;
use App\Http\Controllers\Utils\UtilsController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\QrCode\qrCodeController;
use App\Http\Controllers\Tickets\TicketController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Sponsors\SponsorController;
use App\Http\Controllers\Organizers\OrganizerController;
use App\Http\Controllers\Votes\VoteController;

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

Route::post('vote/x', [VoteController::class, 'store']);



// Auth Routes
Route::prefix('auth')->middleware([CorsConfig::class])->group(function () {
    Route::get('/confirm/{user_id}/{token}', [AuthController::class, 'confirm'])->name('auth.confirm');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register']);
});

Route::prefix('auth')->middleware([CorsConfig::class, Jwt::class])->group(function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

// Utility Routes
Route::prefix('node')->middleware([CorsConfig::class])->group(function () {
    Route::get('country', [UtilsController::class, 'getCountries']);
    Route::get('event-status', [UtilsController::class, 'getEventStatus']);
    Route::get('event-type', [UtilsController::class, 'getEventType']);
    Route::get('ticket-type', [UtilsController::class, 'getTicketType']);
    Route::get('organizer_activity_area', [UtilsController::class, 'getOrganizerActivityArea']);
    Route::get('sponsor_type', [UtilsController::class, 'getSponsorType']);
    Route::get('sponsor_activity_sector', [UtilsController::class, 'getSponsorActivitySector']);
});

// Event Routes
Route::prefix('event')->middleware([CorsConfig::class, Jwt::class])->group(function () {
    Route::post('', [EventController::class, 'store']);
    Route::post('{id}/vote', [EventController::class, 'addVote']);
    Route::get('', [EventController::class, 'getMyEvents']);
    Route::get('{id}', [EventController::class, 'show'])->where(['id' => '[a-z0-9]{24}']);
    Route::post('{id}', [EventController::class, 'update'])->where(['id' => '[a-z0-9]{24}']);
    Route::put('visibility/{id}/{state}', [EventController::class, 'changeVisibility']);
    Route::put('clone/{id}', [EventController::class, 'duplicate'])->where(['id' => '[a-z0-9]{24}']);
    Route::delete('{id}', [EventController::class, 'destroy'])->where(['id' => '[a-z0-9]{24}']);
});

// Ticket Routes
Route::prefix('ticket')->group(function () {
    Route::post('', [TicketController::class, 'store']);
    Route::get('{id}', [TicketController::class, 'show'])->where(['id' => '[a-z0-9]{24}']);
    Route::post('{id}', [TicketController::class, 'update'])->where(['id' => '[a-z0-9]{24}']);
    Route::delete('{id}', [TicketController::class, 'destroy'])->where(['id' => '[a-z0-9]{24}']);
});

// Organizer Routes
Route::prefix('organizer')->group(function () {
    Route::post('', [OrganizerController::class, 'store']);
    Route::get('{id}', [OrganizerController::class, 'show'])->where(['id' => '[a-z0-9]{24}']);
    Route::post('{id}/{eventId}', [OrganizerController::class, 'update'])->where(['id' => '[a-z0-9]{24}']);
    Route::delete('{id}/{eventId}', [OrganizerController::class, 'destroy']);
});

// Sponsor Routes
Route::prefix('sponsor')->group(function () {
    Route::post('', [SponsorController::class, 'store']);
    Route::get('{id_event}', [SponsorController::class, 'getEventAllSponsors'])->where(['id_event' => '[a-z0-9]{24}']);
    Route::get('specificsponsor/{id}', [SponsorController::class, 'show'])->where(['id' => '[a-z0-9]{24}']);
    Route::post('{id}/{eventId}', [SponsorController::class, 'update'])->where(['id' => '[a-z0-9]{24}']);
    Route::delete('{id}/{eventId}', [SponsorController::class, 'destroy']);
});

// QR Code Routes
Route::prefix('qrCode')->group(function () {
    Route::get('event/{eventId}', [qrCodeController::class, 'generateEventQrCode']);
});

// Admin Routes
Route::prefix('admin')->middleware([CheckUserRole::class])->group(function () {
    Route::get('events', [AdminEventController::class, 'index']);
    Route::get('events/filter', [AdminEventController::class, 'filter']);
});

// Vote Routes
Route::prefix('vote')->group(function () {
    Route::post('{id}/participants', [VoteController::class, 'addParticipants']);
});

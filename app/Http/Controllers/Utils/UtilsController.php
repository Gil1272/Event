<?php

namespace App\Http\Controllers\Utils;

use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Events\EventStatus;
use App\Models\Events\EventType;
use Illuminate\Http\Request;
use Monarobase\CountryList\CountryListFacade;


class UtilsController extends Controller
{
    public function getCountries(){
        return response(CountryListFacade::getList('fr', 'json'))->header('Content-Type', 'application/json');
    }

    public function getEventStatus(){
        return response(EventStatus::toArray())->header('Content-Type', 'application/json');
    }

    public function getEventType(){
        return response(EventType::toArray())->header('Content-Type', 'application/json');
    }
}

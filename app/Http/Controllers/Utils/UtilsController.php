<?php

namespace App\Http\Controllers\Utils;

use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Events\EventStatus;
use App\Models\Events\EventType;
use App\Models\Organizers\OrganizerActivityArea;
use App\Models\Sponsors\SponsorActivitySector;
use App\Models\Sponsors\SponsorType;
use App\Models\Tickets\TicketType;
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

    public function getTicketType(){
        return response(TicketType::toArray())->header('Content-Type', 'application/json');
    }

    public function getOrganizerActivityArea(){
        return response(OrganizerActivityArea::toArray())->header('Content-Type', 'application/json');
    }

    public function getSponsorType(){
        return response(SponsorType::toArray())->header('Content-Type', 'application/json');
    }

    public function getSponsorActivitySector(){
        return response(SponsorActivitySector::toArray())->header('Content-Type', 'application/json');
    }
}

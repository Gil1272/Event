<?php

namespace App\Http\Controllers\Utils;

use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Monarobase\CountryList\CountryListFacade;


class CountryController extends Controller
{
    public function get(){
        return response(CountryListFacade::getList('fr', 'json'))->header('Content-Type', 'application/json');
    }
}

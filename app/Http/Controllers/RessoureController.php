<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RessoureController extends Controller
{
    //
    public static function formatAssetsLink($assets){
        $assetsUrls = [];
        foreach($assets as $eachAsset){
            $assetUrl = Storage::disk('public')->url($eachAsset);
            $assetsUrls[] = $assetUrl;
        }
        return $assetsUrls;
    }

    public static function getAllEventsAssetLink($events){

        $eventsWithPhotos = [];

        foreach ($events as $event) {
            $eventWithPhotos = [
                $event->_id =>  [],
            ];

            foreach ($event->photos as $eventPhoto) {

                $photoUrl = Storage::disk('public')->url($eventPhoto); // Supposons que le nom du fichier de la photo soit accessible via $photo->filename
                $eventWithPhotos[ $event->_id ][] = $photoUrl;
            }

            $eventsWithPhotos[] = $eventWithPhotos;
        }

        return $eventsWithPhotos;

    }
}

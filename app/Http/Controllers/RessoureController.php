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
        $eventsWithBanners = [];

        foreach ($events as $event) {
            $eventWithPhotos = [
                $event->_id =>  [],
            ];

             $eventWithBanners = [
                $event->_id =>  [],
            ];

            foreach ($event->photos as $eventPhoto) {

                $photoUrl = Storage::disk('public')->url($eventPhoto); // Supposons que le nom du fichier de la photo soit accessible via $photo->filename
                $eventWithPhotos[ $event->_id ][] = $photoUrl;
            }

            foreach ($event->banners as $eventBanner) {

                $bannerUrl = Storage::disk('public')->url($eventBanner); // Supposons que le nom du fichier de la photo soit accessible via $photo->filename
                $eventWithBanners[ $event->_id ][] = $bannerUrl;
            }

            $eventsWithPhotos[] = $eventWithPhotos;
            $eventsWithBanners[] = $eventWithBanners;
        }

        return [$eventsWithPhotos , $eventsWithBanners];

    }
}

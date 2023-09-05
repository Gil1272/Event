<?php

namespace App\Http\Resources\Sponsors;

use App\Http\Controllers\Sponsors\SponsorController;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SponsorsRessource extends JsonResource
{
    private function setAsset(string $userId, string $eventId, array $assets){
        $newAssets = [];
        foreach ($assets as $asset) {
            $path = $userId.'/'.SponsorController::STORAGE_EVENT.'/'.$eventId.'/'.SponsorController::STORAGE_SPONSOR;
            if(Storage::disk('public')->exists($path)){
                $path = $path.'/'.SponsorController::STORAGE_SPONSOR_LOGO.'/';
                $newAssets['original'] = asset(Storage::url($path.$asset));
                foreach (SponsorController::STORAGE_FORMATS as $format){
                    $newAssets['resizes'][] = asset(Storage::url($path.'resize/'.$format.'-'.$asset));
                }

            }

        }

        return $newAssets;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            "_id" => $this->_id,
            "name" =>  $this->name,
            "type" =>  $this->type,
            "logo" => $this->setAsset($this -> event ->user->_id ,$this -> event->_id,$this->logo),
            "activity_sector" =>  $this->activity_sector,
            "description" =>  $this->description,
            "event" =>  $this ->  event,
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at
        ];
    }
}

<?php

namespace App\Http\Resources\OrganizerResource;

use App\Http\Controllers\Organizers\OrganizerController;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrganizerResource extends JsonResource
{
    private function setAsset(string $userId, string $eventId, array $assets){
        $newAssets = [];
        foreach ($assets as $asset) {
            $path = $userId.'/'.OrganizerController::STORAGE_EVENT.'/'.$eventId.'/'.OrganizerController::STORAGE_ORGANIZER;
            if(Storage::disk('public')->exists($path)){
                $path = $path.'/'.OrganizerController::STORAGE_ORGANIZER_LOGO.'/';
                $newAssets['original'] = asset(Storage::url($path.$asset));
                foreach (OrganizerController::STORAGE_FORMATS as $format){
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
            "logo" =>  $this->setAsset($this -> event ->user->_id ,$this -> event->_id,$this->logo),
            "activity_area" =>  $this->activity_area,
            "description" =>  $this->description,
            "event" =>  $this ->  event,
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at
        ];
    }
}

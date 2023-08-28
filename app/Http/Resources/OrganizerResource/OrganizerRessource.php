<?php

namespace App\Http\Resources\Events;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrganizerResource extends JsonResource
{
    private function setAsset(array $assets){
        $newAssets = [];
        if(Storage::disk('public')->exists($assets)){
            array_push($newAssets,Storage::disk('public')->url($assets));
        } else {
            array_push($newAssets, 'Aucune photo trouvé dans le storage pour ce nom');
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
            "logo" => $this -> setAsset($this->logo),
            "activity_area" =>  $this->activity_area,
            "description" =>  $this->description,
            "event" =>  $this ->  event,
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at
        ];
    }
}

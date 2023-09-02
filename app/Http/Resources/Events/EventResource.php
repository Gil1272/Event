<?php

namespace App\Http\Resources\Events;

use App\Http\Controllers\Events\EventController;
use App\Http\Resources\Tickets\TicketResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
{
    private function setAsset(string $userId, string $eventId, array $assets,bool $isPhoto){
        $newAssets = [];
        foreach ($assets as $asset) {
            $path = $userId.'/'.EventController::STORAGE_EVENT.'/'.$eventId;
            if(Storage::disk('public')->exists($path)){
                if($isPhoto){
                    $path = $path.'/'.EventController::STORAGE_EVENT_PHOTOS.'/';
                }else{
                    $path = $path.'/'.EventController::STORAGE_EVENT_BANNERS.'/';
                }
                $newAssets['original'] = asset(Storage::url($path.$asset));
                foreach (EventController::STORAGE_FORMATS as $format){
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
            "description" =>  $this->description,
            "country" =>  $this->country,
            "place" =>  $this->place,
            "start_date" =>  $this->start_date,
            "end_date" =>  $this->end_date,
            "time_end"=> $this->time_end,
            "user" =>  $this->user,
            "type" =>  $this->type,
            "status" =>  $this->status,
            "published" =>  (bool)$this->published,
            "private" =>  (bool)$this->private,
            "verify" =>  (bool)$this->verify,
            "link" =>  $this->link,
            "banners" =>  $this->setAsset($this->user->_id,$this->_id,$this->banners,false),
            "photos" =>  $this->setAsset($this->user->_id,$this->_id,$this->photos,true),
            "organizers" => $this->organizers ?? null ,
            "sponsors" => $this->sponsors ?? null,
            // "ticket" => TicketResource::collection($this->ticket),
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at
        ];
    }
}

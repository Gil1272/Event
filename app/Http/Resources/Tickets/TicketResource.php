<?php

namespace App\Http\Resources\Tickets;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\QrCode\qrCodeController;
use App\Http\Controllers\Tickets\TicketController;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    private function setAsset(string $userId, string $eventId, array $assets)
    {
        $newAssets = [];
        foreach ($assets as $asset) {
            $path = $userId . '/' . TicketController::STORAGE_EVENT . '/' . $eventId;
            if (Storage::disk('public')->exists($path)) {


                $path = $path . '/' . TicketController::STORAGE_TICKET . '/';



                $newAssets['original'] = asset(Storage::url($path . $asset));
            }
        }

        return $newAssets;
    }




    public function toArray($request)
    {
        /*  return parent::toArray($request); */

        return [
            "_id" => $this->_id,
            "event" => $this->event,
            "type" => $this->type,
            "free" => $this->free,
            "price" => $this->price,
            "number" => $this->number,
            "description" => $this->description,
            "qrcode" => qrCodeController::generateTicketQrCode($this->_id),
            "qrid" => $this->qrid,
            "photos" => $this->setAsset($this->user()->_id, $this->_id, $this->photos),
            "template" => $this-> template,
            "tags" => $this-> tags,
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at
        ];
    }
}

<?php

namespace App\Http\Controllers\QrCode;

use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class qrCodeController extends Controller
{

    const STORAGE_EVENT = "events";
    const STORAGE_QRCODE = "qrcode";


    public function generateEventQrCode($eventId)
    {

        $event = Event::Find($eventId);

        $qrCode = QrCode::size(200)->format('png')->generate($event);

        $qrCodePath = 'public/' . $event->user->_id . '/events/' . $eventId . '/qrCode/';

        if (!Storage::exists($qrCodePath)) {
            Storage::makeDirectory($qrCodePath);
        }

        $filename = $eventId. '_event_qrcode.png';

        Storage::put($qrCodePath . $filename, $qrCode);
        return response()->json(['message' => 'QR code generated and saved to storage.']);
    }




    public function getEventQRCode($eventId){

        $event = Event::Find($eventId);

        $qrCode = [];

        $qrCodePath = $event->user->_id . '/events/' . $eventId . '/qrCode/';

        if(Storage::disk('public')->exists($qrCodePath)){

            $qrCodePath = $qrCodePath.$eventId.'_event_qrcode.png';
            $qrCode['qr_code'] = asset(Storage::url($qrCodePath));

            return $qrCode;
        }

        return $qrCode;

    }

}

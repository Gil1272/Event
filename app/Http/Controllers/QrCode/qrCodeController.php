<?php

namespace App\Http\Controllers\QrCode;

use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Components\Api\JsonResponse;
use App\Models\Tickets\Ticket;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class qrCodeController extends Controller
{

    const STORAGE_EVENT = "events";
    const STORAGE_QRCODE = "qrcode";


    public function generateEventQrCode($eventId)
    {

        $event = Event::find($eventId);

        $qrCode = QrCode::size(200)->format('png')->generate($event->_id);

        $qrCodePath = 'public/' . $event->user->_id . '/events/' . $eventId . '/qrCode/';

        if (!Storage::exists($qrCodePath)) {
            Storage::makeDirectory($qrCodePath);
        }

        $filename = $eventId. '_event_qrcode.png';

        Storage::put($qrCodePath . $filename, $qrCode);

        return JsonResponse::send(
            false,
            "Qr code created! "
        );
    }




    public static function getEventQRCode($eventId){

        $event = Event::find($eventId);

        $qrCode = [];

        $qrCodePath = $event->user->_id . '/events/' . $eventId . '/qrCode/';

        if(Storage::disk('public')->exists($qrCodePath)){

            $qrCodePath = $qrCodePath.$eventId.'_event_qrcode.png';
            $qrCode['qr_code'] = asset(Storage::url($qrCodePath));

            return  $qrCode;

        }

        return $qrCode;

    }


    public function generateTicketQrCode($ticketId){

        $ticket = Ticket::find($ticketId);
        $user = $ticket->user();

        $qrCode = QrCode::size(200)->format('png')->generate($ticket);

        $qrCodePath = 'public/' . $user->_id. '/events/' . $ticket->event->_id . '/qrCode/';

        if (!Storage::exists($qrCodePath)) {
            Storage::makeDirectory($qrCodePath);
        }

        $filename = $ticket->event->_id. '_ticket_qrcode.png';

        Storage::put($qrCodePath . $filename, $qrCode);

        return JsonResponse::send(
            false,
            "Qr code created! "
        );



    }

    public static function getTicketQRCode($ticketId){

        $ticket = Ticket::find($ticketId);
        $user = $ticket->user();

        $qrCode = [];

        $qrCodePath = $user->_id . '/events/' . $ticket->event->_id . '/qrCode/';

        if(Storage::disk('public')->exists($qrCodePath)){

            $qrCodePath = $qrCodePath.$ticket->event->_id.'_ticket_qrcode.png';
            $qrCode['qr_code'] = asset(Storage::url($qrCodePath));

            return  $qrCode;
        }

        return $qrCode;

    }

}

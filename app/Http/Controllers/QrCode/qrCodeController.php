<?php

namespace App\Http\Controllers\QrCode;

use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Models\Tickets\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class qrCodeController extends Controller
{

    const STORAGE_EVENT = "events";
    const STORAGE_QRCODE = "qrcode";


    public function generateEventQrCode($eventId)
    {
        $event = Event::find($eventId);

        $qrCode = QrCode::size(200)->format('png')->generate($event->_id);

        // Return the QR code as a response with appropriate headers
        return Response::make($qrCode, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="event_qrcode.png"',
        ]);
    }



    public static function generateTicketQrCode($ticketId)
    {
        $ticket = Ticket::find($ticketId);


        $qrCode = QrCode::size(200)->format('png')->generate($ticket);

        // Return the QR code as a response with appropriate headers
        return Response::make($qrCode, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="ticket_qrcode.png"',
        ]);
    }


}

<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Components\Api\JsonResponse;
use App\Models\Events\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        ##NB THE LOGIC IS YET FINISH
        $validator =  Validator::make($data,[
            'type' => 'required|exists:ticket_types,id',
            'event' => 'required|exists:events,id',
            'number' => "required|numeric|min:0|not_in:0'"
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                "error" => true,
                "message" => $validator->errors()->messages()
            ]);
        }

        if(!isset($data["free"])){
            $data['price'] = null;
            $data['free'] = true;
        }else{
            if(is_null($data['price']) || $data['price'] <= 0){
                return new JsonResponse([
                    "error" => true,
                    "message" => "Le prix est invalide"
                ]);
            }
            $data['free'] = false;
        }

        $event = Event::find($data["event"]);

        $data['qrid'] = (string)Str::uuid();

        $event->tickets()->create($data);

        return new JsonResponse([
            "error" => false,
            "message" => "Votre ticket a été crée !",
            "data" => [
                "modal" => "#ticket",
                "function" => "getTicket()"
            ]
        ]);
    }


    public function getRelatedToEvent($eventId){
        //
    }

    public function generateQrCode($ticketID,$eventId,$buyerId,$ticketId){

        // $qrcodeFile = $ticket->qrid.".png";
        // QrCode::size(500)->format('png')->generate($qrCodeData, ("assets/img/events/tickets/qrcode/").$qrcodeFile);

    }

    private function getTicketDataForDisplayAndDownload(string $id){

        // $qrCode = QrCode::size(100)->generate($qrCodeData);

        // return [$event,$ticketType,$buyTicket,$qrCode];
    }

    public function generateTicket(string $id){
        //
    }


    public function downloadTicket (string $id)
    {
        //
    }

    public function invalideTickets(Request $request){
        $data = $request->all();
        //
    }

    public function checkTicketByScanner(Request $request){

        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

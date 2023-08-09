<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketType;
use Illuminate\Http\Request;
use App\Components\Api\JsonResponse;
use App\Models\Events\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class TicketController extends Controller
{
    const STORAGE_EVENT = "events";
    const STORAGE_TICKET = "tickets";
    //const STORAGE_FORMATS = ["221_x_170","399_x_311","311_x_208","599_x_311"];
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

        $errorMessage = "vos donnés sont invalides";

        ##NB THE LOGIC IS YET FINISH
        $validator =  Validator::make($data,[
            'type' => 'required',
            'event' => 'required',
            'number' => "required|numeric|min:0|not_in:0'"
        ]);

        if ($validator->fails()) {
            return JsonResponse::send(true,"Erreur",$validator->errors()->messages(),400);
        }

        if(!TicketType::key_exists($request->type)){
            return JsonResponse::send(true,$errorMessage,["type"=>"Le type de ticket n'existe pas"],400);
        }
        $data['type'] = TicketType::get_value($request->type);

        if(!isset($data["free"])){
            $data['price'] = null;
            $data['free'] = true;
        }else{
            if(is_null($data['price']) || $data['price'] <= 0){
                return JsonResponse::send(true,"Le prix est invalide",null,400);
            }
            $data['free'] = false;
        }

        if (is_null($data['number']) || $data['number'] <=0){
            return  new  JsonResponse([
                "error" => true,
                "message" => "Le nombre de ticket est invalide"
            ]);
        }else{
            $data['number'] = $request->number;
        }
        $data['description'] = $request->description;

        $event = Event::find($data["event"]);

        $eventName = $event->name;
        $link_slug =  Str::slug($eventName,'-','fr');

        $data['qrid'] = (string)Str::uuid();
        if($request->hasFile("photos")){
            //iterate throught and upload each file
            //not checking anymore if directory exist or not because storeAs already make it if not
            $photos = $request->file("photos");
            $fileLink = array();
            //for many files
            foreach ($photos as $photo)
            {
                $filename = uniqid().$link_slug.'.'.$photo->getClientOriginalExtension();
                $photo->storeAs(
                    Auth::id()."/".self::STORAGE_EVENT,
                    $filename,
                    ['disk' => 'local']
                );
                $fileLink[] = Auth::id()."/".self::STORAGE_EVENT."/".self::STORAGE_TICKET."/".$filename;
            }
            $data['photos'] = $fileLink;
        }
        else{
            $data["photos"] = []; #put default ticket app photos
        }
        $data["template"] = "";
        $data["tags"] = [];

        $ticket = $event->tickets()->create($data);

        return  JsonResponse::send(false,"Votre ticket a été crée !",$ticket);
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
        $ticket = Ticket::find($id);
        if($ticket)
            return JsonResponse::send(false,null,["ticket"=>$ticket]);
        return JsonResponse::send(true,"Aucun ticket trouvé",null,404);
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
        $data = $request->all();
        $validator =  Validator::make($data,[
            'type' => 'required',
            'event' => 'required',
            'number' => "required|numeric|min:0|not_in:0'"
        ]);
        $errorMessage = "Vos données sont invalides";
        if ($validator->fails())
        {
            return  JsonResponse::send(true,$errorMessage,$validator->errors()->messages(),400);
        }

        $ticket = Ticket::findOrFail($id);

        if(!TicketType::key_exists($request->type)){
            return JsonResponse::send(true,$errorMessage,["type"=>"Le type de ticket n'existe pas"],400);
        }
        $data['type'] = TicketType::get_value($request->type);

        if(!isset($data["free"])){
            $data['price'] = null;
            $data['free'] = true;
        }else{
            if(is_null($data['price']) || $data['price'] <= 0){
                return JsonResponse::send(true,"Le prix est invalide",400);
            }
            $data['free'] = false;
        }

        if (is_null($data['number']) || $data['number'] <=0){
            return JsonResponse::send(true,"Le nombre de ticket est invalide",400);
        }else{
            $data['number'] = $request->number;
        }
        $data['description'] = $request->description;

        $event = Event::find($data["event"]);

        $eventName = $event->name;
        $link_slug =  Str::slug($eventName,'-','fr');
        //remove all photos and upload news
        if ($request->hasFile("photos"))
        {
            //iterate throught and upload each file
            //not checking anymore if directory exist or not because storeAs already make it if not
            $photos = $request->file("photos");
            $fileLink = array();
            //for many files
            foreach ($photos as $photo)
            {
                $filename = uniqid().$link_slug.'.'.$photo->getClientOriginalExtension();
                $photo->storeAs(
                    Auth::id()."/".self::STORAGE_EVENT,
                    $filename,
                    ['disk' => 'local']
                );
                $fileLink[] = Auth::id()."/".self::STORAGE_EVENT."/".self::STORAGE_TICKET."/".$filename;
            }
            $data['photos'] = $fileLink;
        }else
        {
            $data['photos'] = [];
        }
        $data["template"] = "";
        $data["tags"] = [];
        $ticket = $ticket->update($data);

        return JsonResponse::send(false,"Votre ticket a été modifié !",$ticket);
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
        $ticket = Ticket::find($id);
//        dd($ticket);
        if ($ticket)
        {
            $ticket->delete();
            return JsonResponse::send(false,"Le ticket de l'évènement a été supprimé");
        }
        return JsonResponse::send(true,"Le ticket est introuvable !",null,404);
    }
}

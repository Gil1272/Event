<?php

namespace App\Http\Controllers\Events;

use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventStatus;
use App\Models\Events\EventType;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Monarobase\CountryList\CountryListFacade;

class EventController extends Controller
{

    private static function rules() {
        return [
            'name' => 'required',
            'type' => 'required',
            'status' => 'required',
            'description' => 'required',
            'place' => 'required',
            'country' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'time_end' => 'required|date_format:H:i',
        ];
    }

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

        $validator =  Validator::make($data,EventController::rules());
        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true,$errorMessage,$validator->errors()->messages(),400);
        }

        if(!array_key_exists($request->country,CountryListFacade::getList())){
            return JsonResponse::send(true,$errorMessage,["country"=>"Code pays invalide"],400);
        }

        if(!EventType::key_exists($request->type)){
            return JsonResponse::send(true,$errorMessage,["type"=>"Le type d'évènement n'existe pas"],400);
        }
        $data['type'] = EventType::get_value($request->type);

        if(!EventStatus::key_exists($request->status)){
            return JsonResponse::send(true,$errorMessage,["status"=>"Le status de l'évènement n'existe pas"],400);
        }
        $data['status'] = EventStatus::get_value($request->status);

        $link_slug =  Str::slug($data['name'],'-','fr');
        $data['link'] = uniqid()."-".$link_slug;
        $data["published"] = false;
        $data["private"] = false;
        $data["verify"] = false;
        # use resize image plugin after
        if($request->hasFile("photos")){
            //iterate throught and upload each file
            /**
             * Ex :
             * create storage with user id;
             *   if(Storage::disk('local')->exists($user_id)){
             *      pass;
             *    }
             *   Storage::disk('local')->makeDirectory($user_id);
             *  $filename =  uniqid().$link_slug.'.'.$file->getClientOriginalExtension();
             *  $file->move(("assets/img/events"),$filename);
             * */
            $user_id = AUth::id();
            $directory = '/events';
            if (!Storage::disk('local')->exists($user_id))
            {
                #create user directory
                Storage::disk('local')->makeDirectory($user_id.$directory);

            }
            $file = $request->photos;
            $filename =  uniqid().$link_slug.'.'.$file->getClientOriginalExtension();
            $file->move(("assets/img/events"),$filename);
            /**
             * Resize image for different fomart
             *
             */
            $fomart = array(
                1=>'500_x_700',
                2=>'600_x_800'
            );
            foreach ($fomart as $key => $value)
            {
                $path = $user_id.$directory.'/'.$value[$key];
                $width = Str::of($value[$key])->before('_x_');
                $height = Str::of($value[$key])->after('_x_');
                #Let's check if the folder exist ? create
                if(!Storage::disk('local')->exists($path)){
                    #create format directory
                    Storage::disk('local')->makeDirectory($path);
                }
                $newpath = 'assets/img/events/'.$value[$key];
                $file->move(($newpath),$filename);
            }

            $data["photos"] = $filename;
        }else{
            $data["photos"] = []; #put default event app photos
        }

        if($request->hasFile("banners")){
            //iterate thought and upload each file  NB : file shou b
        }else{
            $data["banners"] = []; #default event banner
        }

        $user = User::find(Auth::id());
        $event = $user->events()->create($data);
        if($event){
            return JsonResponse::send(false,"Votre évènement a été créer !",$event);
        }else{
            return JsonResponse::send(true,"L'évènement n'a pas pu être crée");
        }
    }

    public function getMyEvents(){

        $user = User::find(Auth::id());
        return JsonResponse::send(
            false,
            "La liste de mes évènements",
            ["events" => $user->events]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $event =  Event::find($id);
        if($event)
            return JsonResponse::send(false,null,["event"=>$event]);
        return JsonResponse::send(true,"Aucun évènement trouvé",null,404);
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
        $event = Event::find($id);
        // dd($event);
        //think about delete all specifique photos and banners
        // $photos = ltrim(parse_url($event->photos)['path'],"/");
        // $banners = ltrim(parse_url($event->banners)['path'],"/");
        // if (File::exists($photo)) {
        //     File::delete($photo);
        // }

        // if (File::exists($banner)) {
        //     File::delete($banner);
        // }

        if ($event) {
            $event->where('user_id', Auth::id())->first()->delete();
            return JsonResponse::send(false,"L'évènement a été supprimé !");
        }
        return JsonResponse::send(true,"L'évènement est introuvable !",null,404);
    }
}

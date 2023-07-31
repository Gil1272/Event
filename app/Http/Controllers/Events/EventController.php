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

    const STORAGE_EVENT = "events";
    const STORAGE_FORMATS = ["221_x_170","399_x_311","311_x_208","599_x_311"];
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

    /**
     * @LRDparam name string|required
     * @LRDparam description string|required
     * @LRDparam country string|required
     * @LRDparam place String|required
     * @LRDparam type string|required
     * @LRDparam status string|required
     * @LRDparam photos nullable
     * @LRDparam banners nullable
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
            //not checking anymore if directory exist or not because storeAs already make it if not
            $photos = $request->photos;
            foreach ($photos as $photo)
            {
                $filename = uniqid().$link_slug.'.'.$photo->getClientOriginalExtension();
                $photo->storeAs(
                    Auth::id()."/".self::STORAGE_EVENT,
                    $filename,
                    ['disk' => 'local']
                );
                $data['photos'] = $filename;
                //resize file for each format using resize image
                foreach (self::STORAGE_FORMATS as $FORMAT)
                {
                    $width = Str::of($FORMAT)->before('_x_');
                    $height = Str::of($FORMAT)->after('_x_');
                    $width = (int) $width;
                    $height = (int) $height;
                    $photoResized = Image::make($photo)->resize($width,$height);
                    Storage::put(Auth::id()."/".self::STORAGE_EVENT."/".$FORMAT."/".$filename,
                        $photoResized,
                        'public');

                }
            }
        }
        else{
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
        $data = $request->all();

        $validator =  Validator::make($data,EventController::rules());

        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true,$errorMessage,$validator->errors()->messages(),400);
        }

        $event = Event::findOrFail($id);

        $link_slug =  Str::slug($data['name'],'-','fr');
        $data['link'] = uniqid()."-".$link_slug;

        #NB All old files should be delete if a new file is provide
        if($request->hasFile("banners")){
            //iterate thought and upload each file  NB : file shou b
        }else{
            $data["banners"] = []; #default event banner
        }

        if($request->hasFile("banners")){
            //iterate thought and upload each file  NB : file shou b
        }else{
            $data["banners"] = []; #default event banner
        }

        $event = $event->update($data);

        return JsonResponse::send(false,"Votre évènement a été modifié !",$event);

    }

    public function duplicate($id){

        $event = Event::findOrFail($id);

        if($event){
            $photos = $event->photos;
            $banners = $event->banners;

            $eventReplicate = $event->replicate();
            $eventReplicate->create_at = Carbon::now();
            $eventReplicate->update_at = Carbon::now();

            $eventReplicate->published = false;
            $eventReplicate->private = false;
            $eventReplicate->verify = false;
            $eventReplicate->link = uniqid()."-".Str::slug($eventReplicate->name,'-','fr')."-clone";

            $newPhotos = [];
            $newBanners = [];

            #Iterate throug all photos and banners and make copy
            /**
             *File::copy($photo,$asset.$newPhoto);
             *File::copy($banner,$asset.$newBanner);
             */

            $eventReplicate->photos = $newPhotos;
            $eventReplicate->banners = $newBanners;

            $eventReplicate->save();

            if($eventReplicate){
                return JsonResponse::send(false,"L'évènement a été dupliqué !",$eventReplicate);
            }
            return JsonResponse::send(true,"Impossible de dupliquer l'évènement",null,400);
        }
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

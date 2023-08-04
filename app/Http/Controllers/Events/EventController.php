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
use Intervention\Image\Facades\Image;
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
     * Create Event
     * @OA\Post (
     *     path="/event/",
     *     tags={"Event"},
     *     summary="Event Register",
     *     description="Event register",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name","type", "status", "description","place","country","start_date","end_date","time_end"},
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="time"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string")),
     *            ),
     *        ),
     *    ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="string"),
     *                      @OA\Property(property="user",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property(property="published",type="string"),
     *                      @OA\Property(property="private",type="string"),
     *                      @OA\Property(property="verify",type="string"),
     *                      @OA\Property(property="link",type="string"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string"))
     *                 ),
     *                 example={
     *                    "name": "Event Api 3",
     *                   "type": "Atelier",
     *                   "status": "En attente",
     *                   "description": "Event Api description",
     *                   "place": "Cotonou, Bénin",
     *                   "country": "BJ",
     *                   "start_date": "2023-04-05",
     *                   "end_date": "2023-05-05",
     *                   "time_end": "14:30",
     *                   "photos": "64ca25fba7a4fevent-api-3.png",
     *                   "link": "64ca25fba7a02-event-api-3",
     *                   "published": false,
     *                   "private": false,
     *                   "verify": false,
     *                   "banners": "64ca25fba7a4fevent-api-3.png",
     *                   "user_id": "64ca1eadcb2c0000e7001622",
     *                   "updated_at": "2023-08-02T09:46:37.112000Z",
     *                   "created_at": "2023-08-02T09:46:37.112000Z",
     *                   "_id": "64ca25fde937000084006042"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="false"),
     *              @OA\Property(property="msg", type="string", example="Votre évènement a été créer !"),
     *              @OA\Property(property="_id", type="String", example="64b7ba121179c7e2e005ad06"),
     *              @OA\Property(property="name", type="string", example="Event Api"),
     *              @OA\Property(property="description", type="string", example="Event Api description"),
     *              @OA\Property(property="type", type="string", example="WORKSHOP"),
     *              @OA\Property(property="status", type="string", example="PENDING"),
     *              @OA\Property(property="place", type="string", example="Cotonou,Bénin"),
     *              @OA\Property(property="country", type="string", example="BJ"),
     *              @OA\Property(property="start_date", type="string", example="2023-04-05"),
     *              @OA\Property(property="end_date", type="string", example="2023-05-05"),
     *              @OA\Property(property="time_end", type="string", example="14:30"),
     *              @OA\Property(property="photos", type="string", example="64ca25fba7a4fevent-api-3.png"),
     *              @OA\Property(property="link", type="string", example="64ca25fba7a02-event-api-3"),
     *              @OA\Property(property="published", type="boolean", example="false"),
     *              @OA\Property(property="private", type="boolean", example="false"),
     *              @OA\Property(property="verify", type="boolean", example="false"),
     *              @OA\Property(property="banners", type="string", example="64ca25fba7a4fevent-api-3.png"),
     *              @OA\Property(property="user_id", type="string", example="64ca1eadcb2c0000e7001622"),
     *              @OA\Property(property="updated_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *              @OA\Property(property="created_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *          )
     *      )
     * )
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
                $fileLink[] = Auth::id()."/".self::STORAGE_EVENT."/".$filename;
                //resize file for each format using resize image
                foreach (self::STORAGE_FORMATS as $FORMAT)
                {
                    $width = Str::of($FORMAT)->before('_x_');
                    $height = Str::of($FORMAT)->after('_x_');
                    $photoResized = Image::make($photo)->resize($width,$height);
                    Storage::put(Auth::id()."/".self::STORAGE_EVENT."/".$FORMAT."/".$filename,
                        $photoResized,
                        'public');

                }
            }
            $data['photos'] = $fileLink;
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

    /**
     * Show User's Events
     * @OA\Get (
     *     path="/event/",
     *     tags={"Event"},
     *     summary="Get All connected User's Event ",
     *     description="Get connected User's Event",
     *     @OA\Parameter(name="id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="L'évènement a été dupliqué !",
     *          @OA\JsonContent(
     *              @OA\Property(property="events", type="array", @OA\Items(
                    @OA\Property(property="_id", type="String", example="64b7ba121179c7e2e005ad06"),
     *              @OA\Property(property="name", type="string", example="Event Api"),
     *              @OA\Property(property="description", type="string", example="Event Api description"),
     *              @OA\Property(property="type", type="string", example="WORKSHOP"),
     *              @OA\Property(property="status", type="string", example="PENDING"),
     *              @OA\Property(property="place", type="string", example="Cotonou,Bénin"),
     *              @OA\Property(property="country", type="string", example="BJ"),
     *              @OA\Property(property="start_date", type="string", example="2023-04-05"),
     *              @OA\Property(property="end_date", type="string", example="2023-05-05"),
     *              @OA\Property(property="time_end", type="string", example="14:30"),
     *              @OA\Property(property="photos", type="string", example="64ca25fba7a4fevent-api-3.png"),
     *              @OA\Property(property="link", type="string", example="64ca25fba7a02-event-api-3"),
     *              @OA\Property(property="published", type="boolean", example="false"),
     *              @OA\Property(property="private", type="boolean", example="false"),
     *              @OA\Property(property="verify", type="boolean", example="false"),
     *              @OA\Property(property="banners", type="string", example="64ca25fba7a4fevent-api-3.png"),
     *              @OA\Property(property="user_id", type="string", example="64ca1eadcb2c0000e7001622"),
     *              @OA\Property(property="updated_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *              @OA\Property(property="created_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *               ),)
     *
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="hjdd",type="string")
     *          )
     *      ),
     * )
     */
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

    /**
     * Show Events by id
     * @OA\Get (
     *     path="/event/{id}",
     *     tags={"Event"},
     *     summary="Get Event by id ",
     *     description="Get Event by id",
     *     @OA\Parameter(name="id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *    ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="string"),
     *                      @OA\Property(property="user",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property(property="published",type="string"),
     *                      @OA\Property(property="private",type="string"),
     *                      @OA\Property(property="verify",type="string"),
     *                      @OA\Property(property="link",type="string"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string"))
     *                 ),
     *                 example={
     *                    "name": "Event Api 3",
     *                   "type": "Atelier",
     *                   "status": "En attente",
     *                   "description": "Event Api description",
     *                   "place": "Cotonou, Bénin",
     *                   "country": "BJ",
     *                   "start_date": "2023-04-05",
     *                   "end_date": "2023-05-05",
     *                   "time_end": "14:30",
     *                   "photos": "64ca25fba7a4fevent-api-3.png",
     *                   "link": "64ca25fba7a02-event-api-3",
     *                   "published": false,
     *                   "private": false,
     *                   "verify": false,
     *                   "banners": "64ca25fba7a4fevent-api-3.png",
     *                   "user_id": "64ca1eadcb2c0000e7001622",
     *                   "updated_at": "2023-08-02T09:46:37.112000Z",
     *                   "created_at": "2023-08-02T09:46:37.112000Z",
     *                   "_id": "64ca25fde937000084006042"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="L'évènement a été dupliqué !",
     *          @OA\JsonContent(
     *              @OA\Property(property="_id", type="String", example="64b7ba121179c7e2e005ad06"),
     *              @OA\Property(property="name", type="string", example="Event Api"),
     *              @OA\Property(property="description", type="string", example="Event Api description"),
     *              @OA\Property(property="type", type="string", example="WORKSHOP"),
     *              @OA\Property(property="status", type="string", example="PENDING"),
     *              @OA\Property(property="place", type="string", example="Cotonou,Bénin"),
     *              @OA\Property(property="country", type="string", example="BJ"),
     *              @OA\Property(property="start_date", type="string", example="2023-04-05"),
     *              @OA\Property(property="end_date", type="string", example="2023-05-05"),
     *              @OA\Property(property="time_end", type="string", example="14:30"),
     *              @OA\Property(property="photos", type="string", example="64ca25fba7a4fevent-api-3.png"),
     *              @OA\Property(property="link", type="string", example="64ca25fba7a02-event-api-3"),
     *              @OA\Property(property="published", type="boolean", example="false"),
     *              @OA\Property(property="private", type="boolean", example="false"),
     *              @OA\Property(property="verify", type="boolean", example="false"),
     *              @OA\Property(property="banners", type="string", example="64ca25fba7a4fevent-api-3.png"),
     *              @OA\Property(property="user_id", type="string", example="64ca1eadcb2c0000e7001622"),
     *              @OA\Property(property="updated_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *              @OA\Property(property="created_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="hjdd",type="string")
     *          )
     *      ),
     * )
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

    /**
     * Update event
     * @OA\Post (
     *     path="/event/{id}",
     *     tags={"Event"},
     *     summary="Update Event",
     *     description="Update Event",
     *     @OA\Parameter(name="id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="time"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string")),
     *            ),
     *        ),
     *    ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="string"),
     *                      @OA\Property(property="user",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property(property="published",type="string"),
     *                      @OA\Property(property="private",type="string"),
     *                      @OA\Property(property="verify",type="string"),
     *                      @OA\Property(property="link",type="string"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string"))
     *                 ),
     *                 example={
     *                    "name": "Event Api 3",
     *                   "type": "Atelier",
     *                   "status": "En attente",
     *                   "description": "Event Api description",
     *                   "place": "Cotonou, Bénin",
     *                   "country": "BJ",
     *                   "start_date": "2023-04-05",
     *                   "end_date": "2023-05-05",
     *                   "time_end": "14:30",
     *                   "photos": "64ca25fba7a4fevent-api-3.png",
     *                   "link": "64ca25fba7a02-event-api-3",
     *                   "published": false,
     *                   "private": false,
     *                   "verify": false,
     *                   "banners": "64ca25fba7a4fevent-api-3.png",
     *                   "user_id": "64ca1eadcb2c0000e7001622",
     *                   "updated_at": "2023-08-02T09:46:37.112000Z",
     *                   "created_at": "2023-08-02T09:46:37.112000Z",
     *                   "_id": "64ca25fde937000084006042"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Votre évènement a été modifié !",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *          )
     *      )
     * )
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

    /**
     * Duplicate Event
     * @OA\Put (
     *     path="/event/clone/{id}",
     *     tags={"Event"},
     *     summary="Duplicate Event",
     *     description="Duplicate Event",
     *     @OA\Parameter(name="id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *    ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="string"),
     *                      @OA\Property(property="user",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property(property="published",type="string"),
     *                      @OA\Property(property="private",type="string"),
     *                      @OA\Property(property="verify",type="string"),
     *                      @OA\Property(property="link",type="string"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string"))
     *                 ),
     *                 example={
     *                    "name": "Event Api 3",
     *                   "type": "Atelier",
     *                   "status": "En attente",
     *                   "description": "Event Api description",
     *                   "place": "Cotonou, Bénin",
     *                   "country": "BJ",
     *                   "start_date": "2023-04-05",
     *                   "end_date": "2023-05-05",
     *                   "time_end": "14:30",
     *                   "photos": "64ca25fba7a4fevent-api-3.png",
     *                   "link": "64ca25fba7a02-event-api-3",
     *                   "published": false,
     *                   "private": false,
     *                   "verify": false,
     *                   "banners": "64ca25fba7a4fevent-api-3.png",
     *                   "user_id": "64ca1eadcb2c0000e7001622",
     *                   "updated_at": "2023-08-02T09:46:37.112000Z",
     *                   "created_at": "2023-08-02T09:46:37.112000Z",
     *                   "_id": "64ca25fde937000084006042"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="L'évènement a été dupliqué !",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="hjdd",type="string")
     *          )
     *      ),
     * )
     */
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

    /**
     * Delete Event
     * @OA\Delete (
     *     path="/event/{id}",
     *     tags={"Event"},
     *     summary="Delete Event",
     *     description="Delete Event",
     *     @OA\Parameter(name="id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *    ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property( property="description",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="place",type="string"),
     *                      @OA\Property(property="start_date",type="string"),
     *                      @OA\Property(property="end_date",type="string"),
     *                      @OA\Property(property="time_end",type="string"),
     *                      @OA\Property(property="user",type="string"),
     *                      @OA\Property(property="type",type="string"),
     *                      @OA\Property(property="status",type="string"),
     *                      @OA\Property(property="published",type="string"),
     *                      @OA\Property(property="private",type="string"),
     *                      @OA\Property(property="verify",type="string"),
     *                      @OA\Property(property="link",type="string"),
     *                      @OA\Property( property="banners",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="photos",type="array",@OA\Items(type="string"))
     *                 ),
     *                 example={
     *                    "name": "Event Api 3",
     *                   "type": "Atelier",
     *                   "status": "En attente",
     *                   "description": "Event Api description",
     *                   "place": "Cotonou, Bénin",
     *                   "country": "BJ",
     *                   "start_date": "2023-04-05",
     *                   "end_date": "2023-05-05",
     *                   "time_end": "14:30",
     *                   "photos": "64ca25fba7a4fevent-api-3.png",
     *                   "link": "64ca25fba7a02-event-api-3",
     *                   "published": false,
     *                   "private": false,
     *                   "verify": false,
     *                   "banners": "64ca25fba7a4fevent-api-3.png",
     *                   "user_id": "64ca1eadcb2c0000e7001622",
     *                   "updated_at": "2023-08-02T09:46:37.112000Z",
     *                   "created_at": "2023-08-02T09:46:37.112000Z",
     *                   "_id": "64ca25fde937000084006042"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="L'évènement a été supprimé !",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid"
     *      ),
     * )
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

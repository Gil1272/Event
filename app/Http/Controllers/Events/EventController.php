<?php

namespace App\Http\Controllers\Events;

use App\Models\Users\User;
use Illuminate\Support\Str;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Events\EventType;
use App\Models\Events\EventStatus;
use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Events\EventResource;
use Monarobase\CountryList\CountryListFacade;

class EventController extends Controller
{

    const STORAGE_EVENT = "events";
    const STORAGE_EVENT_PHOTOS = "photos";
    const STORAGE_EVENT_BANNERS = "banners";

    const STORAGE_FORMATS = ["221_x_170", "399_x_311", "311_x_208", "599_x_311"];
    private static function rules()
    {
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

    private function uploadFile(Request $request, String $fileAttribut, String $userId, String $eventId, String $fileSlug, String $storeDir): array
    {
        $fileNames = [];

        if ($request->hasFile($fileAttribut)) {
            $photos = $request->file($fileAttribut);

            $eventPath = $userId . '/' . self::STORAGE_EVENT . '/' . $eventId . '/' . $storeDir;
            $eventResizePath = $eventPath . '/' . 'resize';
            Storage::disk('public')->makeDirectory($eventPath);
            Storage::disk('public')->makeDirectory($eventResizePath);
            foreach ($photos as $photo) {
                $filename = uniqid() . '-' . $fileSlug . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs($eventPath, $filename, ['disk' => 'public']);

                #resize upload
                foreach (self::STORAGE_FORMATS as $format) {
                    $width = Str::of($format)->before('_x_');
                    $height = Str::of($format)->after('_x_');
                    $pathResize = storage_path('app/public/' . $eventResizePath . '/' . $format . '-' . $filename);
                    $save = Image::make($photo)->resize($width, $height);
                    // $save = Image::make($photo)->resize($width,$height, function($constraint) {
                    //     $constraint->aspectRatio();
                    // });
                    $save->save($pathResize);
                }

                $fileNames[] = $filename;
            }
        }
        return $fileNames;
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

        $validator =  Validator::make($data, EventController::rules());
        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        if (!array_key_exists($request->country, CountryListFacade::getList())) {
            return JsonResponse::send(true, $errorMessage, ["country" => "Code pays invalide"], 400);
        }

        if (!EventType::key_exists($request->type)) {
            return JsonResponse::send(true, $errorMessage, ["type" => "Le type d'évènement n'existe pas"], 400);
        }
        $data['type'] = EventType::get_value($request->type);

        if (!EventStatus::key_exists($request->status)) {
            return JsonResponse::send(true, $errorMessage, ["status" => "Le status de l'évènement n'existe pas"], 400);
        }
        $data['status'] = EventStatus::get_value($request->status);

        $link_slug =  Str::slug($data['name'], '-', 'fr');
        $data['link'] = uniqid() . "-" . $link_slug;
        $data["published"] = false;
        $data["private"] = false;
        $data["verify"] = false;

        $userID = Auth::id();
        $baseDirectory = $userID;

        $user = User::find($userID);

        $event = $user->events()->create($data);

        if ($event) {
            if (!Storage::disk('public')->exists($baseDirectory)) {
                Storage::disk('public')->makeDirectory($userID);
            }

            $photos = $this->uploadFile($request, 'photos', $userID, $event->id, $link_slug, self::STORAGE_EVENT_PHOTOS);
            $banners = $this->uploadFile($request, 'banners', $userID, $event->id, $link_slug, self::STORAGE_EVENT_BANNERS);

            $eventUpdate = $event->update([
                'photos' => $photos,
                'banners' => $banners,
            ]);

            if ($eventUpdate) {
                return JsonResponse::send(false, "Votre évènement a été créer !", $event);
            }
        }

        return JsonResponse::send(true, "L'évènement n'a pas pu être crée", null, 400);
    }



    public function getMyEvents()
    {

        $user = User::find(Auth::id());
        return JsonResponse::send(
            false,
            "La liste de mes évènements",
            ["events" => EventResource::collection($user->events)]
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
        if ($event)

            return JsonResponse::send(false, null, ["event" => new EventResource($event)]);
        return JsonResponse::send(true, "Aucun évènement trouvé", null, 404);
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

        $validator =  Validator::make($data, EventController::rules());

        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        $event = Event::findOrFail($id);

        $link_slug =  Str::slug($data['name'], '-', 'fr');
        $data['link'] = uniqid() . "-" . $link_slug;
        $data["published"] = false;
        $data["private"] = false;
        $data["verify"] = false;

        $userID = Auth::id();
        $baseDirectory = $userID;

        $user = User::find($userID);

        $event = $user->events();

        if ($event) {


            $PhotosPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_EVENT_PHOTOS;
            $BannersPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_EVENT_BANNERS;
            if (Storage::disk('public')->exists($PhotosPath) || Storage::disk('public')->exists($BannersPath)) {
                $photosPath = storage_path('app/public/' . $PhotosPath); // Construct the full directory path
                $bannersPath = storage_path('app/public/' . $PhotosPath);
                if (File::isDirectory($photosPath) || File::isDirectory($bannersPath)) {
                    File::deleteDirectory($photosPath);
                    File::deleteDirectory($bannersPath);
                } else {
                }
            }


            if (!Storage::disk('public')->exists($baseDirectory)) {
                Storage::disk('public')->makeDirectory($userID);
            }

            $photos = $this->uploadFile($request, 'photos', $userID, $event->id, $link_slug, self::STORAGE_EVENT_PHOTOS);
            $banners = $this->uploadFile($request, 'banners', $userID, $event->id, $link_slug, self::STORAGE_EVENT_BANNERS);

            $eventUpdate = $event->update([
                'photos' => $photos,
                'banners' => $banners,
            ]);

        }


        $event = $event->update($data);

        return JsonResponse::send(false, "Votre évènement a été modifié.", $event);
    }




    public function duplicate($id)
    {

        $event = Event::findOrFail($id);

        if ($event) {
            $photos = $event->photos;
            $banners = $event->banners;

            $eventReplicate = $event->replicate();
            $eventReplicate->create_at = Carbon::now();
            $eventReplicate->update_at = Carbon::now();

            $eventReplicate->published = false;
            $eventReplicate->private = false;
            $eventReplicate->verify = false;
            $eventReplicate->link = uniqid() . "-" . Str::slug($eventReplicate->name, '-', 'fr') . "-clone";

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

            if ($eventReplicate) {
                return JsonResponse::send(false, "L'évènement a été dupliqué !", $eventReplicate);
            }
            return JsonResponse::send(true, "Impossible de dupliquer l'évènement", null, 400);
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
            $event->sponsors()->delete();
            $event->organizers()->delete();
            $event->tickets()->delete();

            $dir = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id;
            if (Storage::disk('public')->exists($dir)) {
                // Construct the full directory path
                $dir = storage_path('app/public/' . $dir);
                if (File::isDirectory($dir)) {
                    File::deleteDirectory($dir);
                } else {
                }
            }

            return JsonResponse::send(false, "L'évènement a été supprimé !");
        }

        return JsonResponse::send(true, "L'évènement est introuvable !", null, 404);
    }
}

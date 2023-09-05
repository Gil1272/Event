<?php

namespace App\Http\Controllers\Organizers;

use Illuminate\Support\Str;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Organizers\Organizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Organizers\OrganizerActivityArea;
use App\Http\Resources\OrganizerResource\OrganizerResource;

class OrganizerController extends Controller
{
    //
    const STORAGE_EVENT = "events";
    const STORAGE_ORGANIZER = "organizers";
    const STORAGE_ORGANIZER_LOGO = "logo";

    const STORAGE_FORMATS = ["221_x_170", "399_x_311", "311_x_208", "599_x_311"];
    private static function rules()
    {
        return [

            'name' => 'required',
            'activity_area' => 'required',
            'description' => 'required',
            'event' => 'required'
        ];
    }

    private static function rulesRefactored()
    {
        return [

            'name' => 'required',
            'activity_area' => 'required',
            'description' => 'required',
        ];
    }

    private function uploadFile(Request $request, String $fileAttribut, String $userId, String $eventId, String $fileSlug, String $storeDir): array
    {
        $fileNames = [];

        if ($request->hasFile($fileAttribut)) {
            $logo = $request->file($fileAttribut);

            $organizerPath = $userId . '/' . self::STORAGE_EVENT . '/' . $eventId . '/' . self::STORAGE_ORGANIZER . '/' . $storeDir;
            $eventResizePath = $organizerPath . '/' . 'resize';
            Storage::disk('public')->makeDirectory($organizerPath);
            Storage::disk('public')->makeDirectory($eventResizePath);


            $filename = uniqid() . '-' . $fileSlug . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs($organizerPath, $filename, ['disk' => 'public']);

            #resize upload
            foreach (self::STORAGE_FORMATS as $format) {
                $width = Str::of($format)->before('_x_');
                $height = Str::of($format)->after('_x_');
                $pathResize = storage_path('app/public/' . $eventResizePath . '/' . $format . '-' . $filename);
                $save = Image::make($logo)->resize($width, $height);
                // $save = Image::make($photo)->resize($width,$height, function($constraint) {
                //     $constraint->aspectRatio();
                // });
                $save->save($pathResize);
            }

            $fileNames[] = $filename;
        }

        return $fileNames;
    }

    /**
     * Store a newly created organizer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $data = $request->all();

        $validator =  Validator::make($data, OrganizerController::rules());
        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        if (!OrganizerActivityArea::key_exists($request->activity_area)) {
            return JsonResponse::send(true, $errorMessage, ["type" => "Le secteur d'activité n'existe pas"], 400);
        }
        $data['activity_area'] = OrganizerActivityArea::get_value($request->activity_area);
        $link_slug =  Str::slug($data['name'], '-', 'fr');



        $event = Event::find($data['event']);
        if ($event) {

            $userID = $event->user->_id;

            $logo = $this->uploadFile($request, 'logo', $userID, $event->_id, $link_slug, self::STORAGE_ORGANIZER_LOGO);

            $data['logo'] = $logo;
        }
        $organizer =  $event->organizers()->create($data);
        if ($organizer) {
            return JsonResponse::send(false, "Votre organizer a été créer !", $organizer);
        } else {
            return JsonResponse::send(true, "L'organizer n'a pas pu être crée");
        }
    }



    /**
     * Update an existing created organizer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id, $eventId)
    {
        $data = $request->all();

        $validator =  Validator::make($data, OrganizerController::rulesRefactored());
        $errorMessage = "vos données sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        if (!OrganizerActivityArea::key_exists($request->activity_area)) {
            return JsonResponse::send(true, $errorMessage, ["type" => "Le secteur d'activité n'existe pas"], 400);
        }
        $data['activity_area'] = OrganizerActivityArea::get_value($request->activity_area);


        $link_slug =  Str::slug($data['name'], '-', 'fr');





        $organizer = Organizer::find($id);
        $event = Event::find($eventId);
        if ($organizer) {
            $organizerPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_ORGANIZER . '/' . self::STORAGE_ORGANIZER_LOGO;
            if (Storage::disk('public')->exists($organizerPath)) {
                $directoryPath = storage_path('app/public/' . $organizerPath); // Construct the full directory path

                if (File::isDirectory($directoryPath)) {
                    File::deleteDirectory($directoryPath);
                } else {
                }
            }
            $userID = $event->user->_id;

            $logo = $this->uploadFile($request, 'logo', $userID, $event->_id, $link_slug, self::STORAGE_ORGANIZER_LOGO);

            $data['logo'] = $logo;

            $organizer =  $organizer->update($data);
            return JsonResponse::send(false, "Votre organizer a été modifié !", $organizer);
        }
    }


    /**
     * Display specifique sponsors resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        $organizer =  Organizer::find($id);
        if ($organizer)

            return JsonResponse::send(false, null, ["organizer" => new OrganizerResource($organizer)]);
        return JsonResponse::send(true, "Aucun organizer trouvé", null, 404);
    }


    /**
     * Remove the organizer resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id, $eventId)
    {

        $organizer = Organizer::find($id);
        $event = Event::find($eventId);
        if ($organizer) {
            $organizer->delete();
            $organizerPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_ORGANIZER . '/' . self::STORAGE_ORGANIZER_LOGO;
            if (Storage::disk('public')->exists($organizerPath)) {
                $directoryPath = storage_path('app/public/' . $organizerPath); // Construct the full directory path

                if (File::isDirectory($directoryPath)) {
                    File::deleteDirectory($directoryPath);
                } else {
                }
            }
            return JsonResponse::send(false, "L'organizer de l'évènement a été supprimé");
        } else {
            return JsonResponse::send(true, "L'organizer est introuvable !", null, 404);
        }
    }
}

<?php

namespace App\Http\Controllers\Sponsors;

use Illuminate\Support\Str;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Models\Sponsors\Sponsor;
use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Sponsors\SponsorActivitySector;
use App\Http\Resources\Sponsors\SponsorsRessource;

class SponsorController extends Controller
{
    //

    const STORAGE_EVENT = "events";
    const STORAGE_SPONSOR_LOGO = "logo";
    const STORAGE_SPONSOR = "sponsors";
    const STORAGE_FORMATS = ["221_x_170", "399_x_311", "311_x_208", "599_x_311"];


    private static function rules()
    {
        return [

            'name' => 'required',
            'type' => 'required',
            'activity_sector' => 'required',
            'event' => 'required',
            'description' => 'required',

        ];
    }

    private static function rulesWithoutEvent()
    {
        return [

            'name' => 'required',
            'type' => 'required',
            'activity_sector' => 'required',
            'description' => 'required',

        ];
    }

    private function uploadFile(Request $request, String $fileAttribut, String $userId, String $eventId, String $fileSlug, String $storeDir): array
    {
        $fileNames = [];

        if ($request->hasFile($fileAttribut)) {
            $logo = $request->file($fileAttribut);

            $organizerPath = $userId . '/' . self::STORAGE_EVENT . '/' . $eventId . '/' . self::STORAGE_SPONSOR . '/' . $storeDir;
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
     * Store a newly created sponsor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $data = $request->all();

        $validator =  Validator::make($data, SponsorController::rules());
        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        if (!SponsorType::key_exists($request->type)) {
            return JsonResponse::send(true, $errorMessage, ["type" => "Le type de sponsor n'existe pas"], 400);
        }
        $data['type'] = SponsorType::get_value($request->type);

        if (!SponsorActivitySector::key_exists($request->activity_sector)) {
            return JsonResponse::send(true, $errorMessage, ["activity_sector" => "Le secteur d'activité n'est pas prit en compte"], 400);
        }
        $data['activity_sector'] = SponsorActivitySector::get_value($request->activity_sector);

        $link_slug =  Str::slug($data['name'], '-', 'fr');



        $event = Event::find($data['event']);
        if ($event) {

            $userID = $event->user->_id;

            $logo = $this->uploadFile($request, 'logo', $userID, $event->_id, $link_slug, self::STORAGE_SPONSOR_LOGO);

            $data['logo'] = $logo;
        }
        $sponsors =  $event->sponsors()->create($data);
        if ($sponsors) {
            return JsonResponse::send(false, "Votre sponsor a été créer !", $sponsors);
        } else {
            return JsonResponse::send(true, "Le sponsor n'a pas pu être crée");
        }
    }


    /**
     * Get all the sponsors of specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function getEventAllSponsors($id)
    {

        $event = Event::find($id);
        if ($event) {
            $sponsors = $event->sponsors()->get();
            return JsonResponse::send(
                false,
                "La liste des sponsors",
                ["sponsors" => $sponsors]
            );
        } else {
            return JsonResponse::send(true, "Aucun sponsor trouvé", null, 404);
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
        $sponsor =  Sponsor::find($id);
        if ($sponsor)

            return JsonResponse::send(false, null, ["sponsor" => new SponsorsRessource($sponsor)]);
        return JsonResponse::send(true, "Aucun sponsor trouvé", null, 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, $id , $eventId)
    {

        $data = $request->all();

        $validator =  Validator::make($data, SponsorController::rulesWithoutEvent());
        $errorMessage = "vos donnés sont invalides";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        if (!SponsorType::key_exists($request->type)) {
            return JsonResponse::send(true, $errorMessage, ["type" => "Le type de sponsor n'existe pas"], 400);
        }
        $data['type'] = SponsorType::get_value($request->type);

        if (!SponsorActivitySector::key_exists($request->activity_sector)) {
            return JsonResponse::send(true, $errorMessage, ["activity_sector" => "Le secteur d'activité n'est pas prit en compte"], 400);
        }
        $data['activity_sector'] = SponsorActivitySector::get_value($request->activity_sector);

        $link_slug =  Str::slug($data['name'], '-', 'fr');


        $sponsors = Sponsor::find($id);
        $event = Event::find($eventId);
        if ($sponsors) {
            $organizerPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_SPONSOR . '/' . self::STORAGE_SPONSOR_LOGO;
            if (Storage::disk('public')->exists($organizerPath)) {
                $directoryPath = storage_path('app/public/' . $organizerPath); // Construct the full directory path

                if (File::isDirectory($directoryPath)) {
                    File::deleteDirectory($directoryPath);
                } else {
                }
            }
            $userID = $event->user->_id;

            $logo = $this->uploadFile($request, 'logo', $userID, $event->_id, $link_slug, self::STORAGE_SPONSOR_LOGO);

            $data['logo'] = $logo;

            $sponsors =  $sponsors->update($data);
            return JsonResponse::send(false, "Votre sponsor a été modifié !", $sponsors);
        }
    }



    /**
     * Remove the sponsor resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id , $eventId)
    {

        $sponsor = Sponsor::find($id);
        $event = Event::find($eventId);
        if ($sponsor) {
            $sponsor->delete();
            $organizerPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_SPONSOR . '/' . self::STORAGE_SPONSOR_LOGO;
            if (Storage::disk('public')->exists($organizerPath)) {
                $directoryPath = storage_path('app/public/' . $organizerPath); // Construct the full directory path

                if (File::isDirectory($directoryPath)) {
                    File::deleteDirectory($directoryPath);
                } else {
                }
            }
            return JsonResponse::send(false, "Le sponsor de l'évènement a été supprimé");
        } else {
            return JsonResponse::send(true, "Le sponsor est introuvable !", null, 404);
        }

    }
}

<?php

namespace App\Http\Controllers\Organizers;

use Illuminate\Support\Str;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Http\Resources\OrganizerResource\OrganizerResource;
=======
use App\Http\Resources\OrganizerResource\OrganizerRessource;
>>>>>>> a82cdd61e1f1cf8f2ecf0d96356f369c2538a099
use App\Models\Organizers\Organizer;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Organizers\OrganizerActivityArea;

class OrganizerController extends Controller
{
    //
    const STORAGE_ORGANIZER = "organizers";
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



        if ($request->hasFile("logo")) {

            $logo = $request->file("logo");
            $fileLink = '';

            $filename = uniqid() . $link_slug . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs(
                'public/' . Auth::id() . "/" . self::STORAGE_ORGANIZER,
                $filename,
                ['disk' => 'local']
            );
            $fileLink = Auth::id() . "/" . self::STORAGE_ORGANIZER . "/" . $filename;
            //resize file for each format using resize image
            foreach (self::STORAGE_FORMATS as $FORMAT) {
                $width = Str::of($FORMAT)->before('_x_');
                $height = Str::of($FORMAT)->after('_x_');
                $photoResized = Image::make($logo)->resize($width, $height);
                Storage::put(
                    'public/' . Auth::id() . "/" . self::STORAGE_ORGANIZER . "/" . $FORMAT . "/" . $filename,
                    $photoResized,
                    'public'
                );
            }

            $data['logo'] = $fileLink;
        } else {
            $data['logo'] = [];
        }

        $event = Event::find($data['event']);
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

    public function update(Request $request, $id)
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



        if ($request->hasFile("logo")) {

            $organizer = Organizer::find($id);
            if ($organizer) {

                if (Storage::disk('public')->exists('public' . $organizer->logo)) {
                    Storage::disk('public')->delete('public' . $organizer->logo);
                }
                if (Storage::disk('local')->exists('public' . $organizer->logo)) {
                    Storage::disk('local')->delete('public' . $organizer->logo);
                }
            }


            $logo = $request->file("logo");
            $fileLink = '';

            $filename = uniqid() . $link_slug . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs(
                'public/' . Auth::id() . "/" . self::STORAGE_ORGANIZER,
                $filename,
                ['disk' => 'local']
            );
            $fileLink = Auth::id() . "/" . self::STORAGE_ORGANIZER . "/" . $filename;
            //resize file for each format using resize image
            foreach (self::STORAGE_FORMATS as $FORMAT) {
                $width = Str::of($FORMAT)->before('_x_');
                $height = Str::of($FORMAT)->after('_x_');
                $photoResized = Image::make($logo)->resize($width, $height);
                Storage::put(
                    'public/' . Auth::id() . "/" . self::STORAGE_ORGANIZER . "/" . $FORMAT . "/" . $filename,
                    $photoResized,
                    'public'
                );
            }


            $data['logo'] = $fileLink;
        } else {
            $data['logo'] = [];
        }

        $organizer = Organizer::find($id);

        if ($organizer) {
            $organizer =  $organizer->update($data);
            return JsonResponse::send(false, "Votre organizer a été modifié !", $organizer);
        } else {
            return JsonResponse::send(true, "L'organizer n'a pas pu être modifié , car introuvable");
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

            return JsonResponse::send(false, null, ["organizer" => new OrganizerRessource($organizer)]);
        return JsonResponse::send(true, "Aucun organizer trouvé", null, 404);
    }


    /**
     * Remove the organizer resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {

        $organizer = Organizer::find($id);
        if ($organizer) {
            $organizer->delete();
            if (Storage::disk('public')->exists('public' . $organizer->logo)) {
                Storage::disk('public')->delete('public' . $organizer->logo);
            }
            if (Storage::disk('local')->exists('public' . $organizer->logo)) {
                Storage::disk('local')->delete('public' . $organizer->logo);
            }
            return JsonResponse::send(false, "L'organizer de l'évènement a été supprimé");
        } else {
            return JsonResponse::send(true, "L'organizer est introuvable !", null, 404);
        }
    }
}

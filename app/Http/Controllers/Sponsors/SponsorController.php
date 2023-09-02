<?php

namespace App\Http\Controllers\Sponsors;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Components\Api\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Sponsors\SponsorsRessource;
use App\Models\Events\Event;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorType;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Sponsors\SponsorActivitySector;

class SponsorController extends Controller
{
    //


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



        if ($request->hasFile("logo")) {

            $logo = $request->file("logo");
            $fileLink = '';

            $filename = uniqid() . $link_slug . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs(
                'public/' . Auth::id() . "/" . self::STORAGE_SPONSOR,
                $filename,
                ['disk' => 'local']
            );
            $fileLink = Auth::id() . "/" . self::STORAGE_SPONSOR . "/" . $filename;
            //resize file for each format using resize image
            foreach (self::STORAGE_FORMATS as $FORMAT) {
                $width = Str::of($FORMAT)->before('_x_');
                $height = Str::of($FORMAT)->after('_x_');
                $photoResized = Image::make($logo)->resize($width, $height);
                Storage::put(
                    'public/' . Auth::id() . "/" . self::STORAGE_SPONSOR . "/" . $FORMAT . "/" . $filename,
                    $photoResized,
                    'public'
                );
            }

            $data['logo'] = $fileLink;
        } else {
            $data['logo'] = [];
        }

        $event = Event::find($data['event']);
        $sponsor =  $event->sponsors()->create($data);
        if ($sponsor) {
            return JsonResponse::send(false, "Votre sponsor a été créer !", $sponsor);
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


    public function update(Request $request, $id)
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



        if ($request->hasFile("logo")) {


            $sponsor = Sponsor::find($id);
            if ($sponsor) {

                if (Storage::disk('public')->exists('public' . $sponsor->logo)) {
                    Storage::disk('public')->delete('public' . $sponsor->logo);
                }
                if (Storage::disk('local')->exists('public' . $sponsor->logo)) {
                    Storage::disk('local')->delete('public' . $sponsor->logo);
                }
            }


            $logo = $request->file("logo");
            $fileLink = '';

            $filename = uniqid() . $link_slug . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs(
                'public/' . Auth::id() . "/" . self::STORAGE_SPONSOR,
                $filename,
                ['disk' => 'local']
            );
            $fileLink = Auth::id() . "/" . self::STORAGE_SPONSOR . "/" . $filename;
            //resize file for each format using resize image
            foreach (self::STORAGE_FORMATS as $FORMAT) {
                $width = Str::of($FORMAT)->before('_x_');
                $height = Str::of($FORMAT)->after('_x_');
                $photoResized = Image::make($logo)->resize($width, $height);
                Storage::put(
                    'public/' . Auth::id() . "/" . self::STORAGE_SPONSOR . "/" . $FORMAT . "/" . $filename,
                    $photoResized,
                    'public'
                );
            }


            $data['logo'] = $fileLink;
        } else {
            $data['logo'] = [];
        }

        $sponsor = Sponsor::find($id);


        if ($sponsor) {
            $sponsor = $sponsor->update($data);
            return JsonResponse::send(false, "Votre sponsor a été modifié !", $sponsor);
        } else {
            return JsonResponse::send(true, "Votre sponsor est introuvable !",);
        }
    }



    /**
     * Remove the sponsor resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {

        $sponsor = Sponsor::find($id);
        if ($sponsor) {
            $sponsor->delete();
            if (Storage::disk('public')->exists('public' . $sponsor->logo)) {
                Storage::disk('public')->delete('public' . $sponsor->logo);
            }
            if (Storage::disk('local')->exists('public' . $sponsor->logo)) {
                Storage::disk('local')->delete('public' . $sponsor->logo);
            }
            return JsonResponse::send(false, "Le sponsor de l'évènement a été supprimé");
        } else {
            return JsonResponse::send(true, "Le sponsor est introuvable !", null, 404);
        }
    }
}

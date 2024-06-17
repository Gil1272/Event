<?php

namespace App\Http\Controllers\Events;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use App\Models\Users\User;
use Illuminate\Support\Str;
use App\Models\Votes\Vote;
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

    /**
     * Get validation rules.
     *
     * @return array
     */
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

    /**
     * Upload files and handle resizing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $fileAttribut
     * @param  string  $userId
     * @param  string  $eventId
     * @param  string  $fileSlug
     * @param  string  $storeDir
     * @return array
     */
    private function uploadFile(Request $request, string $fileAttribut, string $userId, string $eventId, string $fileSlug, string $storeDir): array
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

                // Resize upload
                foreach (self::STORAGE_FORMATS as $format) {
                    $width = Str::of($format)->before('_x_');
                    $height = Str::of($format)->after('_x_');
                    $pathResize = storage_path('app/public/' . $eventResizePath . '/' . $format . '-' . $filename);
                    $save = Image::make($photo)->resize($width, $height);
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
     *
     * @bodyParam name string required The name of the event.
     * @bodyParam type string required The type of the event.
     * @bodyParam status string required The status of the event.
     * @bodyParam description string required The description of the event.
     * @bodyParam place string required The place of the event.
     * @bodyParam country string required The country of the event.
     * @bodyParam start_date string required The start date of the event (YYYY-MM-DD format).
     * @bodyParam end_date string required The end date of the event (YYYY-MM-DD format).
     * @bodyParam time_end string required The end time of the event (HH:MM format).
     *
     * @response {
     *    "error": false,
     *    "message": "Your event has been created!",
     *    "data": {
     *        "id": 1,
     *        "name": "Example Event",
     *        ...
     *    }
     * }
     * @response 400 {
     *    "error": true,
     *    "message": "Your data is invalid",
     *    "errors": {
     *        "name": ["The name field is required."],
     *        ...
     *    }
     * }
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, self::rules());
        $errorMessage = "Your data is invalid";

        if ($validator->fails()) {
            return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
        }

        if (!array_key_exists($request->country, CountryListFacade::getList())) {
            return JsonResponse::send(true, $errorMessage, ["country" => "Invalid country code"], 400);
        }

        if (!EventType::key_exists($request->type)) {
            return JsonResponse::send(true, $errorMessage, ["type" => "Event type does not exist"], 400);
        }
        $data['type'] = EventType::get_value($request->type);

        if (!EventStatus::key_exists($request->status)) {
            return JsonResponse::send(true, $errorMessage, ["status" => "Event status does not exist"], 400);
        }
        $data['status'] = EventStatus::get_value($request->status);

        $link_slug = Str::slug($data['name'], '-', 'fr');
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
                return JsonResponse::send(false, "Your event has been created!", $event);
            }
        }

        return JsonResponse::send(true, "Failed to create the event", null, 400);
    }

    /**
     * Get all events of the authenticated user.
     *
     * @authenticated
     *
     * @response {
     *    "error": false,
     *    "message": "List of my events",
     *    "data": {
     *        "events": [
     *            {
     *                "id": 1,
     *                "name": "Example Event",
     *                ...
     *            },
     *            ...
     *        ]
     *    }
     * }
     *
     * @return \Illuminate\Http\Response
     */
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
     * Display the specified event.
     *
     * @urlParam id integer required The ID of the event.
     *
     * @response {
     *    "error": false,
     *    "data": {
     *        "event": {
     *            "id": 1,
     *            "name": "Example Event",
     *            ...
     *        }
     *    }
     * }
     * @response 404 {
     *    "error": true,
     *    "message": "Event not found"
     * }
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
 * Met à jour la ressource spécifiée dans le stockage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id L'ID de l'événement à mettre à jour.
 * @return \Illuminate\Http\Response
 *
 * @bodyParam name string required Le nom de l'événement.
 * @bodyParam type string required Le type de l'événement.
 * @bodyParam status string required Le statut de l'événement.
 * @bodyParam description string required La description de l'événement.
 * @bodyParam place string required Le lieu de l'événement.
 * @bodyParam country string required Le pays de l'événement.
 * @bodyParam start_date string required La date de début de l'événement (format YYYY-MM-DD).
 * @bodyParam end_date string required La date de fin de l'événement (format YYYY-MM-DD).
 * @bodyParam time_end string required L'heure de fin de l'événement (format HH:MM).
 *
 * @response {
 *    "error": false,
 *    "message": "Votre événement a été modifié.",
 *    "data": {
 *        "id": 1,
 *        "name": "Example Event",
 *        ...
 *    }
 * }
 * @response 400 {
 *    "error": true,
 *    "message": "Vos données sont invalides",
 *    "errors": {
 *        "name": ["Le champ name est requis."],
 *        ...
 *    }
 * }
 * @response 404 {
 *    "error": true,
 *    "message": "L'événement n'a pas été trouvé."
 * }
 */
public function update(Request $request, $id)
{
    // Récupérer toutes les données de la requête
    $data = $request->all();

    // Valider les données avec les règles définies dans EventController
    $validator = Validator::make($data, EventController::rules());
    $errorMessage = "Vos données sont invalides";

    // Vérifier si la validation échoue
    if ($validator->fails()) {
        return JsonResponse::send(true, $errorMessage, $validator->errors()->messages(), 400);
    }

    // Trouver l'événement par son ID
    $event = Event::findOrFail($id);

    // Générer un lien unique pour l'événement
    $link_slug = Str::slug($data['name'], '-', 'fr');
    $data['link'] = uniqid() . "-" . $link_slug;
    $data["published"] = false;
    $data["private"] = false;
    $data["verify"] = false;

    // Récupérer l'ID de l'utilisateur authentifié
    $userID = Auth::id();
    $baseDirectory = $userID;

    // Récupérer l'utilisateur et ses événements
    $user = User::find($userID);
    $event = $user->events()->find($id);

    if ($event) {
        // Définir les chemins pour les photos et bannières de l'événement
        $PhotosPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_EVENT_PHOTOS;
        $BannersPath = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id . '/' . self::STORAGE_EVENT_BANNERS;

        // Supprimer les répertoires existants s'ils existent
        if (Storage::disk('public')->exists($PhotosPath) || Storage::disk('public')->exists($BannersPath)) {
            $photosPath = storage_path('app/public/' . $PhotosPath);
            $bannersPath = storage_path('app/public/' . $BannersPath);
            if (File::isDirectory($photosPath) || File::isDirectory($bannersPath)) {
                File::deleteDirectory($photosPath);
                File::deleteDirectory($bannersPath);
            }
        }

        // Créer le répertoire de base s'il n'existe pas
        if (!Storage::disk('public')->exists($baseDirectory)) {
            Storage::disk('public')->makeDirectory($userID);
        }

        // Télécharger les nouvelles photos et bannières
        $photos = $this->uploadFile($request, 'photos', $userID, $event->id, $link_slug, self::STORAGE_EVENT_PHOTOS);
        $banners = $this->uploadFile($request, 'banners', $userID, $event->id, $link_slug, self::STORAGE_EVENT_BANNERS);

        // Mettre à jour l'événement avec les nouvelles données
        $eventUpdate = $event->update([
            'photos' => $photos,
            'banners' => $banners,
        ]);

        $event = $event->update($data);

        // Retourner une réponse JSON de succès
        return JsonResponse::send(false, "Votre événement a été modifié.", $event);
    }

    return JsonResponse::send(true, "L'événement n'a pas été trouvé.", null, 404);
}


  /**
 * Duplique l'événement spécifié.
 *
 * @urlParam id integer required L'ID de l'événement.
 *
 * @response {
 *    "error": false,
 *    "message": "Event duplicated successfully!",
 *    "data": {
 *        "id": 2,
 *        "name": "Duplicated Event",
 *        ...
 *    }
 * }
 * @response 400 {
 *    "error": true,
 *    "message": "Failed to duplicate event",
 * }
 * @response 404 {
 *    "error": true,
 *    "message": "Event not found"
 * }
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function duplicate($id)
{
    $event = Event::findOrFail($id);

    if ($event) {
        $eventReplicate = $event->replicate();
        $eventReplicate->created_at = Carbon::now();
        $eventReplicate->updated_at = Carbon::now();

        $eventReplicate->published = false;
        $eventReplicate->private = false;
        $eventReplicate->verify = false;
        $eventReplicate->link = uniqid() . "-" . Str::slug($eventReplicate->name, '-', 'fr') . "-clone";

        $newPhotos = [];
        $newBanners = [];

        // Itérer à travers toutes les photos et bannières et les copier
        foreach ($event->photos as $photo) {
            $newPhoto = 'copied_' . $photo;
            File::copy($photo, 'public/' . $newPhoto);
            $newPhotos[] = $newPhoto;
        }

        foreach ($event->banners as $banner) {
            $newBanner = 'copied_' . $banner;
            File::copy($banner, 'public/' . $newBanner);
            $newBanners[] = $newBanner;
        }

        $eventReplicate->photos = $newPhotos;
        $eventReplicate->banners = $newBanners;

        $eventReplicate->save();

        return JsonResponse::send(false, "L'événement a été dupliqué !", $eventReplicate);
    }

    return JsonResponse::send(true, "Impossible de dupliquer l'événement", null, 400);
}


/**
 * Supprime la ressource spécifiée du stockage.
 *
 * @urlParam id integer required L'ID de l'événement.
 *
 * @response {
 *    "error": false,
 *    "message": "Event deleted successfully!"
 * }
 * @response 404 {
 *    "error": true,
 *    "message": "Event not found"
 * }
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function destroy($id)
{
    $event = Event::find($id);

    if ($event) {
        $event->where('user_id', Auth::id())->first()->delete();
        $event->sponsors()->delete();
        $event->organizers()->delete();
        $event->tickets()->delete();

        $dir = $event->user->_id . '/' . self::STORAGE_EVENT . '/' . $event->_id;
        if (Storage::disk('public')->exists($dir)) {
            $dir = storage_path('app/public/' . $dir);
            if (File::isDirectory($dir)) {
                File::deleteDirectory($dir);
            }
        }

        return JsonResponse::send(false, "L'événement a été supprimé !");
    }

    return JsonResponse::send(true, "L'événement est introuvable !", null, 404);
}

/** 
 * Ajoute un vote à un événement existant.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id L'ID de l'événement auquel ajouter le vote.
 * @bodyParam name string required Le nom du vote.
 * @bodyParam description string required La description du vote.
 *
 * @response {
 *    "error": false,
 *    "message": "Le vote a été ajouté avec succès",
 *    "data": {
 *        "id": 1,
 *        "name": "Example Vote",
 *        ...
 *    }
 * }
 * @response 400 {
 *    "error": true,
 *    "message": "Les données fournies sont invalides",
 *    "errors": {
 *        "name": ["Le champ name est requis."],
 *        ...
 *    }
 * }
 * @response 404 {
 *    "error": true,
 *    "message": "Événement introuvable"
 * }
 * @return \Illuminate\Http\Response
 */
public function addVote(Request $request, $id)
{
       // Récupérer toutes les données de la requête
       $data = $request->all();

       // Définir les règles de validation
       $validator = Validator::make($data, [
           'name' => 'required|string',
           'description' => 'required|string'
       ]);
       
       $errorMessage = "Les données fournies sont invalides";
       
    // Vérifier si la validation échoue
    if ($validator->fails()) {
        return JsonResponse::send(true,$errorMessage,$validator->errors()->messages(),400);
    }
    
    // Trouver l'événement par ID
    $event = Event::find($id);
    if($event){
      // Créer un nouveau vote
    $vote = new Vote([
        'name' => $data['name'],
        'description' => $data['description']
    ]);

    // Associer le vote à l'événement
    $event->votes()->save($vote);

    // Retourner une réponse JSON de succès
    return JsonResponse::send(
        false,
        "Le vote a été ajouté avec succès",
        $vote
    );
    }
    else{
        return JsonResponse::send(
             true,
            "L'événement n'existe pas",
             null,
             404);
    }

}



}
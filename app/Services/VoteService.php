<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Vote;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteService
{
    /**
     * Add a vote to an existing event.
     *
     * @param  Request  $request
     * @param  int  $eventId
     * @return array
     */
    public function addVote(Request $request, $eventId)
    {
        // Récupérer toutes les données de la requête
        $data = $request->all();

        // Définir les règles de validation
        $validator = Validator::make($data, [
            'name' => 'required|string',
        ]);
        $errorMessage = "Les données fournies sont invalides";

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => $errorMessage,
                'errors' => $validator->errors()->messages(),
                'status' => 400
            ];
        }

        // Trouver l'événement par ID
        $event = Event::find($eventId);
        
        // Vérifier si l'événement existe
        if (!$event) {
            return [
                'error' => true,
                'message' => "L'événement n'existe pas",
                'status' => 404
            ];
        }

        // Utiliser une transaction pour garantir l'intégrité des données
        try {
            DB::beginTransaction();

            // Créer un nouveau vote
            $vote = new Vote([
                'eventId' => $eventId,
                'name' => $data['name'],
            ]);

            // Associer le vote à l'événement
            $event->votes()->save($vote);

            DB::commit();

            return [
                'error' => false,
                'message' => "Le vote a été ajouté avec succès",
                'data' => $vote,
                'status' => 200
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => true,
                'message' => "Une erreur est survenue lors de l'ajout du vote",
                'status' => 500
            ];
        }
    }
}

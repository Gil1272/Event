<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
 * Add a participant to a vote.
 *
 * @urlParam voteId integer required The ID of the vote.
 *
 * @bodyParam name string required The name of the participant.
 * @bodyParam detail string required The detail of the participant.
 * @bodyParam image string required The image of the participant.
 *
 * @response {
 *    "error": false,
 *    "message": "Participant added successfully!",
 *    "data": {
 *        "id": 1,
 *        "name": "John Doe",
 *        "detail": "Participant details",
 *        "image": "participant_image.jpg",
 *        ...
 *    }
 * }
 * @response 404 {
 *    "error": true,
 *    "message": "Vote not found"
 * }
 * @response 400 {
 *    "error": true,
 *    "message": "Your data is invalid",
 *    "errors": {
 *        "name": ["The name field is required."],
 *        ...
 *    }
 * }
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $voteId
 * @return \Illuminate\Http\Response
 */

    public function addParti(Request $request, $voteId)
    {
        // Récupérer toutes les données de la requête
        $data = $request->all();
    
        // Définir les règles de validation
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'detail' => 'required|string',
            'image' => 'required|string',
            'voteId' => 'required|exists:votes,id',
        ]);
        $errorMessage = "Les données fournies sont invalides";
    
        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $errorMessage,
                'errors' => $validator->errors()->messages()
            ], 400);
        }
    
        // Trouver l'événement par ID
        $event = Vote::find($voteId);
        
        // Vérifier si l'événement existe
        if (!$event) {
            return response()->json([
                'error' => true,
                'message' => "L'événement n'existe pas",
            ], 404);
        }
    
        // Créer un nouveau participant
        $participant = new Vote([
            'voteId' => $eventId,
            'name' => $data['name'],
            'image' => $data['image'],
            'detail' => $data['detail']
        ]);
    
        // Associer le vote à l'événement
        $vote->participants()->save($participant);
    
        // Retourner une réponse JSON de succès
        return response()->json([
            'error' => false,
            'message' => "Le vote a été ajouté avec succès",
            'data' => $participant,
        ]);
    }
}

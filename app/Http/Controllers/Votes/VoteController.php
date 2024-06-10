<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoteController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/events/{eventId}/votes",
 *     tags={"Votes"},
 *     summary="Add a vote to an event",
 *     @OA\Parameter(
 *         name="eventId",
 *         in="path",
 *         required=true,
 *         description="ID of the event to add the vote to",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="description", type="string", example="Vote for the event"),
 *         ),
 *     ),
 *     @OA\Response(response="200", description="Vote added successfully"),
 *     @OA\Response(response="400", description="Invalid input data"),
 *     @OA\Response(response="404", description="Event not found"),
 * )
 */

    public function addVote(Request $request, $eventId)
{
    // Récupérer toutes les données de la requête
    $data = $request->all();

    // Définir les règles de validation
    $validator = Validator::make($data, [
        'name' => 'required|string',
        'dscription' => 'required|string',
        'eventId' => 'required|exists:events,id',
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
    $event = Event::find($eventId);
    
    // Vérifier si l'événement existe
    if (!$event) {
        return response()->json([
            'error' => true,
            'message' => "L'événement n'existe pas",
        ], 404);
    }

    // Créer un nouveau vote
    $vote = new Vote([
        'eventId' => $eventId,
        'name' => $data['name'],
        'description' => $data['description']
    ]);

    // Associer le vote à l'événement
    $event->votes()->save($vote);

    // Retourner une réponse JSON de succès
    return response()->json([
        'error' => false,
        'message' => "Le vote a été ajouté avec succès",
        'data' => $vote
    ]);
}
}

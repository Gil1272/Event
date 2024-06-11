<?php

namespace App\Http\Controllers\Votes;

use Illuminate\Http\Request;
use App\Models\Votes\Vote;
use App\Models\Participants\Participant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VoteController extends Controller
{
/**
 * Ajoute des participants à un vote existant.
 *
 * @urlParam id integer required L'ID du vote.
 * @bodyParam participants array required La liste des participants à ajouter.
 * @bodyParam participants.*.name string required Le nom du participant. Exemple: John Doe
 * @bodyParam participants.*.detail string required Le détail du participant. Exemple: Information sur le participant
 *
 * @OA\Post(
 *     path="/votes/{id}/participants",
 *     summary="Ajouter des participants à un vote existant",
 *     tags={"Votes"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="ID du vote"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="detail", type="string", example="Information sur le participant")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Participants ajoutés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Données invalides",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vote non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="boolean"),
 *             @OA\Property(property="message", type="string")
 *         )
 *     )
 * )
 * }
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function addParticipants(Request $request, $id)
{
    // Récupérer toutes les données de la requête
    $data = $request->all();

    // Définir les règles de validation
    $validator = Validator::make($data, [
        'participants' => 'required|array',
        'participants.*.name' => 'required|string',
        'participants.*.detail' => 'required|string'
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

    // Trouver le vote par ID
    $vote = Vote::find($id);

    // Vérifier si le vote existe
    if (!$vote) {
        return response()->json([
            'error' => true,
            'message' => "Le vote n'existe pas",
        ], 404);
    }

    // Ajouter les participants
    $participants = [];
    foreach ($data['participants'] as $participantData) {
        $participant = new Participant([
            'name' => $participantData['name'],
            'detail' => $participantData['detail'],
            'vote_id' => $vote->id
        ]);
        $vote->participants()->save($participant);
        $participants[] = $participant;
    }

    // Retourner une réponse JSON de succès
    return response()->json([
        'error' => false,
        'message' => "Les participants ont été ajoutés avec succès",
        'data' => $participants
    ]);
}

}

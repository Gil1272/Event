<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Models\Users\UserVerify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    const PROMOTER = "promoter";
    const ADMIN = "admin";
    const CUSTOMER = "customer";
    const USER = "user";


    public function confirmAccount(int $user_id, string $token)
    {
        $userVerify = UserVerify::where("user_id", $user_id)->where("token", $token)->firstOrFail();

        if ($userVerify) {
            $data['token'] = null;
            $user = User::find($user_id);
            $userVerify->update($data);

            // Assuming you have a deep link or custom URL scheme for your mobile app,
            // you can redirect to it like this
            $mobileAppDeepLink = 'eventvote://login'; // Replace with your app's actual deep link
            return redirect()->away($mobileAppDeepLink);
        }
    }



    public function password(Request $request)
    {
        //TODO
        /**
         * check if user mail exist
         * if mail exist generate token and store it into userverify table ! send the token via mail to user
         * send confirm message to user in the front
         *
         * if the user mail don't exist send message to the user about it in the front
         * in the two cases you must return JsonResponse
         */
        return null;
    }

    public function confirmPassword(Request $request, string $token)
    {


        //TODO
        /**
         * Befor display the blade view to change the password check if the token is correct except rediret to 404
         * if token is good display the view and let user submit the new password
         * if the password is submit you may like to create a new method to store them in the database or user the
         * current method and make the different between http method GET to display the blade view and POST to
         * confirm pass
         * After the password confirm set the password_token in the userVerify table to null
         * and connect user with new password and redirect them to friendly interface
         */
        return null;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $usersDel = User::destroy($id);
        if ($usersDel) {
            return new JsonResponse([
                "error" => false,
                "message" => "Le compte utilisateur a été supprimé",
                "data" => [
                    // "url" => route("")
                ],
            ]);
        }
    }
}

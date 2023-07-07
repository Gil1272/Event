<?php

namespace App\Http\Controllers\Auths;


use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Components\Api\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Users\UserController;
use App\Mail\Users\RegisterMail;
use App\Models\Users\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends UserController
{


    public static function messages() {
        return [
            'phone_number.required' => 'Votre numero n\'est pas valide',
            'email.required' => 'Invalide mail',
            'name.required' => 'Votre prénom est obligatoire',
            'civility.required' => 'Votre civilité est obligatoire',
            'country.required' => 'Votre pays  est obligatoire',
            'firstname.required' => 'Votre nom est obligatoire',
            'password.required' => 'Votre mot de passe est obligatoire',
        ];
    }

    public static function rules() {
        return [
            'phone_number' => 'required|unique:users,phone_number',
            'name' => 'required',
            'firstname' => 'required',
            'password' => 'required',
            "civility" => "required",
            "country" => "required",
            'email' => "required|unique:users,email",
        ];
    }

    public function register(Request $request) {

        $data = $request->all();

        $validator =  Validator::make($data, AuthController::rules(),AuthController::messages());

        if ($validator->fails()) {
            return JsonResponse::send(true,"Vos informations d'inscription sont incorrectes !",$validator->errors()->messages(),400);
        } else {

            $data["password"] = Hash::make($data['password']);

            $user_type = [self::USER];

            if(isset($data['promoter'])){
                $user_type[] = self::PROMOTER;
            }else{
                $user_type[] = self::CUSTOMER;
            }

            $data["user_type"] = $user_type;

            $user = User::create($data);

            $token = uniqid(Str::random(32), true);

            $user->userVerify()
            ->create(['token' => $token]);

            // Mail::to($data['email'])->send(new RegisterMail([
            //     "header" => "Salut ! ".$data['name']." ".$data['firstname'],
            //     "message" => "Vous venez de vous inscrire sur ".env("APP_NAME")."! Merci de cliquer sur le lien dessous pour confirmer votre compte",
            //     "link" => route("Auth#Confirm",["user_Id"=>$user->id,"token"=>$token]),
            //     "linkText" => "Confirmer mon compte"
            // ]));

            return new JsonResponse([
                "error" => false,
                "message" => "Merci d'avoir créé votre compte. Veuillez consulter votre boîte mail afin de confirmer votre compte et commencer à utiliser ".env("APP_NAME")
            ]);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {

        $credentials = $request->only(['phone_number', 'password']);

        $validator =  Validator::make($credentials,[
            'phone_number' => 'required|exists:users,phone_number',
            'password' => 'required',
        ],[
            'phone_number.required' => 'Votre numero n\'est pas valide',
            'name.required' => 'Votre prénom est obligatoire',
        ]);

        if ($validator->fails()) {
            return JsonResponse::send(true,"Vos informations de conection sont invalides",$validator->errors()->messages(),400);
        }

        $user = User::where("phone_number", $credentials["phone_number"])->firstOrFail();

        $userVerify = $user->userVerify->token;
        if (!is_null($userVerify)) {
            return new JsonResponse([
                "error" => true,
                "message" => "Veuillez consulter votre boîte mail .
                    Vous avez sans doute reçu un message.
                    Veuillez au besoin verifier vos spam. ",
            ]);
        }

        if (! $token = JWTAuth::attempt($credentials)) {
            return JsonResponse::send(true,"Vérifier vos informations de connexion",null,401);
        }

        $user = Auth::user();

        return JsonResponse::send(false,"vous etes authentifié",[
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'infos' => [
                "user" => $user,
                "user_verify" => $user->userVerify,
            ]
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request) {

        $user = JWTAuth::authenticate($request->token);
        return JsonResponse::send(false,null,[
            "user" => $user,
            "user_type" => $user->userType,
            "user_verify" => $user->userVerify,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        JWTAuth::invalidate();

        return JsonResponse::send(false,"Déconnexion réussie !");
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return JsonResponse::send(false,"votre jeton est régénérer",[
            'access_token' => JWTAuth::refresh(),
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }

}

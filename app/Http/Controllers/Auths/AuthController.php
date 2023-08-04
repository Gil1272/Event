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
use Monarobase\CountryList\CountryListFacade;

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


    /**
     * @OA\Post(
     * path="/api/register",
     * operationId="Register",
     * tags={"User"},
     * summary="User Register",
     * description="User Register required unique phone_number and email",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name","email", "password", "password_confirmation"},
     *               @OA\Property(property="phone_number", type="text"),
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="firstname", type="text"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="civility", type="text"),
     *               @OA\Property(property="email", type="text"),
     *            ),
     *        ),
     *    ), @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="_id",type="string"),
     *                      @OA\Property( property="phone_number",type="string"),
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property(property="firstname",type="string"),
     *                      @OA\Property(property="password",type="string"),
     *                      @OA\Property(property="civility",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="email",type="string"),
     *                      @OA\Property(property="user_type",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="updated_at", type="string"),
     *                      @OA\Property(property="created_at", type="string")
     *
     *                 ),
     *                 example={
     *                       "_id": "64ca1eadcb2c0000e7001622",
     *                       "phone_number": "66248499",
     *                       "name": "Jean",
     *                       "firstname": "Doe",
     *                       "password": "$2y$10$mbNy17DeKqz.OVVSCganDOREhugSpE1n6H7Pc9PbXMyadAtFwrlVa",
     *                       "civility": "Mr",
     *                       "country": "BJ",
     *                       "email": "jean.doe@mail.com",
     *                       "user_type": {
     *                       "user",
     *                       "customer"
     *                       },
     *                       "updated_at": "2023-08-02T09:15:25.653Z",
     *                       "created_at":"2023-08-02T09:15:25.653Z"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="_id", type="String", example="64b7ba121179c7e2e005ad06"),
     *              @OA\Property(property="phone_number", type="string", example="66248499"),
     *              @OA\Property(property="name", type="string", example="Joe"),
     *              @OA\Property(property="fisrtname", type="string", example="Doe"),
     *              @OA\Property(property="password", type="string", example="$2y$10$mbNy17DeKqz.OVVSCganDOREhugSpE1n6H7Pc9PbXMyadAtFwrlVa"),
     *              @OA\Property(property="civility", type="string", example="Mr"),
     *              @OA\Property(property="country", type="string", example="BJ"),
     *              @OA\Property(property="email", type="string", example="jean.doe@mail.com")
     *          )
     *      ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function register(Request $request) {

        $data = $request->all();

        $validator =  Validator::make($data, AuthController::rules(),AuthController::messages());

        if ($validator->fails()) {
            return JsonResponse::send(true,"Vos informations d'inscription sont incorrectes !",$validator->errors()->messages(),400);
        } else {
            if(!array_key_exists($request->country,CountryListFacade::getList())){
                return JsonResponse::send(true,"Vos informations d'inscription sont incorrectes !",["country"=>"pays code invalide"],400);
            }
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

            return JsonResponse::send(false,"Merci d'avoir créé votre compte. Veuillez consulter votre boîte mail afin de confirmer votre compte et commencer à utiliser ".env("APP_NAME"));
        }
    }


    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    private function findUsername()
    {
        $login = request()->input('login');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="Login",
     * tags={"User"},
     * summary="Login Register",
     * description="User Register required  phone_number and email at identify",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"identify","password"},
     *               @OA\Property(property="identify", type="text"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ), @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(property="_id",type="string"),
     *                      @OA\Property( property="phone_number",type="string"),
     *                      @OA\Property(property="name",type="string"),
     *                      @OA\Property(property="firstname",type="string"),
     *                      @OA\Property(property="password",type="string"),
     *                      @OA\Property(property="civility",type="string"),
     *                      @OA\Property(property="country",type="string"),
     *                      @OA\Property(property="email",type="string"),
     *                      @OA\Property(property="user_type",type="array",@OA\Items(type="string")),
     *                      @OA\Property(property="updated_at", type="string"),
     *                      @OA\Property(property="created_at", type="string")
     *
     *                 ),
     *                 example={
     *                       "_id": "64ca1eadcb2c0000e7001622",
     *                       "phone_number": "66248499",
     *                       "name": "Jean",
     *                       "firstname": "Doe",
     *                       "password": "$2y$10$mbNy17DeKqz.OVVSCganDOREhugSpE1n6H7Pc9PbXMyadAtFwrlVa",
     *                       "civility": "Mr",
     *                       "country": "BJ",
     *                       "email": "jean.doe@mail.com",
     *                       "user_type": {
     *                       "user",
     *                       "customer"
     *                       },
     *                       "updated_at": "2023-08-02T09:15:25.653Z",
     *                       "created_at":"2023-08-02T09:15:25.653Z"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
         *          @OA\Property (property="data",type="array",@OA\Items(
         *              @OA\Property (property="access_token",type="string",example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL3YxL2F1dGgvbG9naW4iLCJpYXQiOjE2OTA5ODg3MTgsImV4cCI6MTY5MDk5MjMxOCwibmJmIjoxNjkwOTg4NzE4LCJqdGkiOiJTTUxiMEpLblVQVUZIVkNFIiwic3ViIjoiNjRjYTFlYWRjYjJjMDAwMGU3MDAxNjIyIiwicHJ2IjoiNGFjMDVjMGY4YWMwOGYzNjRjYjRkMDNmYjhlMWY2MzFmZWMzMjJlOCJ9.a9qYwCD5X61wR4SyUyMSCJlm09vOES1-Fnp_D0V7HDY"),
         *              @OA\Property (property="token_type",type="string",example="bearer"),
         *              @OA\Property (property="expire_in",type="string",example="3600"),
         *              @OA\Property (property="infos",type="array",@OA\Items(
         *                          @OA\Property(property="user",type="array",@OA\Items(
         *                                          @OA\Property(property="_id", type="String", example="64b7ba121179c7e2e005ad06"),
         *                                          @OA\Property(property="phone_number", type="string", example="66248499"),
         *                                          @OA\Property(property="name", type="string", example="Joe"),
         *                                          @OA\Property(property="fisrtname", type="string", example="Doe"),
         *                                          @OA\Property(property="password", type="string", example="$2y$10$mbNy17DeKqz.OVVSCganDOREhugSpE1n6H7Pc9PbXMyadAtFwrlVa"),
         *                                          @OA\Property(property="civility", type="string", example="Mr"),
         *                                          @OA\Property(property="country", type="string", example="BJ"),
         *                                          @OA\Property(property="email", type="string", example="jean.doe@mail.com"),
         *                                          @OA\Property(property="user_type",type="string",example="user,customer"),
         *                                          @OA\Property(property="updated_at",type="string",example="2023-08-02T09:15:25.653000Z"),
         *                                          @OA\Property(property="created_at",type="string",example="2023-08-02T09:15:25.653000Z"),
         *                                          @OA\Property(property="user_verify",type="array",@OA\Items(
         *                                                      @OA\Property(property="_id",type="string",example="64ca1eaecb2c0000e7001623"),
         *                                                      @OA\Property(property="token",type="string",example="null"),
         *                                                      @OA\Property(property="user_id",type="string",example="64ca1eaecb2c0000e7001623"),
         *                                                      @OA\Property(property="updated_at",type="string",example="2023-08-02T09:15:26.307000Z"),
         *                                                      @OA\Property(property="created_at",type="string",example="2023-08-02T09:15:26.307000Z"),
     *                                                         ),
     *                                              ),
     *                              ),),
     *                 ),),
     *          ),),
     *
     *
     *      ),),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function login(Request $request) {

        $identify = request()->input('identify');

        if(is_null($identify))

        if(is_null($identify)){
            return JsonResponse::send(true,"Vos informations de conection sont invalides",["identify"=>["identify est requit"]],400);
        };

        $fieldType = filter_var($identify, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        request()->merge([$fieldType => $identify]);

        $credentials = $request->only([$fieldType, 'password']);

        $entity_message = $fieldType == 'email' ? "Votre mail n'est pas valide" : "votre numero de téléphone n'est pas valide";

        $validator =  Validator::make($credentials,[
            $fieldType => "required|exists:users,$fieldType",
            'password' => 'required',
        ],[
            $fieldType.".required" => $entity_message,
            'password.required' => 'Votre mot de passe est obligatoire',
        ]);

        if ($validator->fails()) {
            return JsonResponse::send(true,"Vos informations de conection sont invalides",$validator->errors()->messages(),400);
        }

        $user = User::where($fieldType, $credentials[$fieldType])->firstOrFail();

        $userVerify = $user->userVerify->token;
        if (!is_null($userVerify)) {
            return JsonResponse::send(true,"Veuillez consulter votre boîte mail. Vous avez sans doute reçu un message. Veuillez au besoin verifier vos spam. ");
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

<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Users\User;
use Illuminate\Http\Request;
use App\Components\Api\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Check if the authenticated user has the role of 'admin'
            if (in_array('admin', User::find($user->id)->user_type)) {
                return $next($request);
            } else {
                return JsonResponse::send(true, "User not in Admin", null, 403);
            }
        } catch (\Throwable $th) {
            return JsonResponse::send(true, "User not connected", null, 401);
        }

    }
}

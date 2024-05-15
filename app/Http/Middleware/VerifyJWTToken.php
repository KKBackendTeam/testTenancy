<?php

namespace App\Http\Middleware;

use Closure;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class VerifyJWTToken
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
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()
                    ->json([
                        'content' => 'user_not_found',
                        'status' => 404
                    ]);
            }

        } catch (JWTException $e) {

            if ($e instanceof TokenExpiredException) {

                $refreshed = JWTAuth::refresh(JWTAuth::getToken());
                JWTAuth::setToken($refreshed)->toUser();

                return response()
                    ->json([
                        'content' => 'token_expired',
                        'status' => $e->getStatusCode(),
                        'old_token' => JWTAuth::getToken(),
                        'token' => $refreshed
                    ]);

            } else if ($e instanceof TokenInvalidException) {

                return response()
                    ->json([
                        'content' => 'token_invalid',
                        'status' => $e->getStatusCode()
                    ]);
            } else {
                return response()
                    ->json([
                        'content' => 'token_is_required',
                    ]);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class FileAccessMiddleware
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
                        'content' => 'fileNotFound'
                    ]);
            }
        } catch (JWTException $e) {

            if ($e instanceof TokenExpiredException) {

                return response()
                    ->json([
                        'content' => 'fileNotFound'
                    ]);
            } else if ($e instanceof TokenInvalidException) {

                return response()
                    ->json([
                        'content' => 'fileNotFound'
                    ]);
            } else {
                return response()
                    ->json([
                        'content' => 'fileNotFound',
                    ]);
            }
        }
        return $next($request);
    }
}

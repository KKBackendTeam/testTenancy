<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminMiddleware
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
        $roles = $this->getRequiredRoleForRoute($request);

        $super = JWTAuth::parseToken()->authenticate();

        if ($super) {
            if ($roles == "SuperAdmin" && $this->superAdmin($super)) {
                return $next($request);
            } else {
               return response()->json(['saved' => false, 'statusCode' => 2310]);
            }
        } else {
            return response()->json(['saved' => false, 'statusCode' => 2310]);
        }
    }

    private function getRequiredRoleForRoute($route)
    {
        $actions = $route->route()->getAction();
        return isset($actions['onlyFor']) ? $actions['onlyFor'] : null;
    }

    private function superAdmin($super)
    {
        return $super->roleStatus == 2 ? true : false;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AgencyMiddleware
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

        $agency = JWTAuth::parseToken()->authenticate();

        if ($agency) {

            if ($roles == "Agency" && agencyAdmin($agency)) {

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
        return isset($actions['roles']) ? $actions['roles'] : null;
    }

    private function agencyAdmin($agency)
    {
        return $agency->roleStatus == 1 ? true : false;
    }
}

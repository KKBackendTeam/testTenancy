<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public $roleArray = ['Applicant', 'Agency', 'Admin'];

    public function handle($request, Closure $next)
    {
        $roles = $this->getRequiredRoleForRoute($request->route());

        if (Auth::check()) {
            if ($this->roleArray[Auth::user()->roleStatus] == $roles || !$roles) {

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
        $actions = $route->getAction();
        return isset($actions['roles']) ? $actions['roles'] : null;
    }
}

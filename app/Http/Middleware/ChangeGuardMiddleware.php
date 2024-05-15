<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ChangeGuardMiddleware
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
        config(['jwt.user' => 'App\Applicantbasic']);
        config(['auth.defaults.guard' => 'applicant']);

        return $next($request);
    }
}

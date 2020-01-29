<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Str;

class RestrictMobileAppToCaregivers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (is_mobile_app()
            && Auth::check()
            && Auth::user()->role_type !== 'caregiver'
            && !Str::contains($request->getUri(), 'logout')
        ) {
            return response(view('errors.mobile_app_restricted'));
        }

        return $next($request);
    }
}

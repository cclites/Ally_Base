<?php
namespace App\Http\Middleware;

use Closure;

class CheckActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check that the user has been approved
        if($request->user() && $request->user()->active)
        {
            return $next($request);
        }
        return redirect('/notice/inactive');
    }
}

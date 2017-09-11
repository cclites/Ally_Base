<?php
namespace App\Http\Middleware;

use Closure;

class CheckRole{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the required roles from the route
        $roles = $this->getRequiredRoleForRoute($request->route());

        // Check if a role is required for the route, and
        // if so, ensure that the user has that role.
        if(in_array($request->user()->role, $roles))
        {
            return $next($request);
        }
        abort(403, 'Insufficient Role: You do not have access to this resource.');
    }

    private function getRequiredRoleForRoute($route)
    {
        $actions = $route->getAction();
        return isset($actions['roles']) ? $actions['roles'] : null;
    }
}
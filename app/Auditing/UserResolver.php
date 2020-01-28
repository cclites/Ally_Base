<?php
namespace App\Auditing;

use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use phpDocumentor\Reflection\Types\Integer;

class UserResolver implements \OwenIt\Auditing\Contracts\UserResolver
{

    /**
     * Resolve the ID of the logged User.
     *
     * @return mixed|null
     */
    public static function resolve()
    {
        if ($user = Auth::user()) {
            if ($user->isImpersonating()) {
               $user = Auth::user()->impersonator();
            }
        }

        return $user instanceof Authenticatable ? $user : null;
    }
}
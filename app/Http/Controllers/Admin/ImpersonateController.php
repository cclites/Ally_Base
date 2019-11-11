<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\User;
use App\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    public function impersonate(User $user)
    {
        if(auth()->user()->role_type === 'office_user'){
            \Session::put('impersonate', $user->id);
            $session_key = \Auth::getName();

            if ($session_key) {
                \Session::put($session_key, $user->id);
            }
        }else{
            $user->impersonate();
        }

        return redirect('/');
    }

    public function stopImpersonating()
    {
        if (\Auth::check() && auth()->user()->impersonator()) {
            auth()->user()->stopImpersonating();
            return redirect()->route('admin.users.index');
        }
        abort(403);
    }

    public function business(Business $business)
    {
        $user = $business->users()->active()->orderBy('id')->first();
        if (\Auth::check() && auth()->user()->impersonator()) {
            auth()->user()->stopImpersonating();
        }
        return redirect()->action('Admin\ImpersonateController@impersonate', [$user->id]);
    }

}

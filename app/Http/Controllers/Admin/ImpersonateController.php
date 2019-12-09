<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\User;
use App\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    public function impersonate(User $user)
    {
        $user->impersonate();
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

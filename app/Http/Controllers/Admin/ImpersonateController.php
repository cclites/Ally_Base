<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Business;
use App\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    /**
     * Impersonate the given user.
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function impersonate(User $user)
    {
        if (! is_admin()) {
            abort(401);
        }

        if (auth()->user()->impersonator()) {
            // Automatically stop impersonating if already impersonating
            // an office user.
            auth()->user()->stopImpersonating();
            return redirect()->route('business.impersonate', [$user->id]);
        }

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

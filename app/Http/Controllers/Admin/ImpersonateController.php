<?php

namespace App\Http\Controllers\Admin;

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
        if (auth()->user()->impersonator()) {
            auth()->user()->stopImpersonating();
            return redirect()->route('admin.users.index');
        }
        abort(403);
    }

}

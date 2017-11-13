<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    public function impersonate(User $user)
    {
        $user->impersonate();
        redirect('/');
    }

    public function stopImpersonating()
    {
        auth()->user()->stopImpersonating();
        redirect()->route('admin.users.index');
    }

}

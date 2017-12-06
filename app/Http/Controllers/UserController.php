<?php

namespace App\Http\Controllers;

use App\User;

class UserController extends Controller
{
    public function phoneNumbers(User $user)
    {
        $type = $user->role_type;
        $user->load('phoneNumbers');

        // include a placeholder for the primary number if one doesn't already exist
        if ($user->phoneNumbers->where('type', 'primary')->count() == 0) {
            $user->phoneNumbers->prepend(['type' => 'primary', 'extension' => '', 'number' => '']);
        }

        // include a placeholder for the billing number if one doesn't already exist
        if ($type == 'client' && $user->phoneNumbers->where('type', 'billing')->count() == 0) {
            $user->phoneNumbers->prepend(['type' => 'billing', 'extension' => '', 'number' => '']);
        }

        return response()->json($user->phoneNumbers);
    }
}

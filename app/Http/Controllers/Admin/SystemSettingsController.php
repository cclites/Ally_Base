<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function show(){

        $systemSettings = \DB::table('system_settings')->get();

        return view_component('admin-system-settings', 'Registry Emails', ['systemSettings' => $systemSettings]);
    }
}

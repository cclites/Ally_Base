<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role_type == 'office_user') return redirect()->route('business.schedule');
        if (auth()->user()->role_type == 'caregiver') return redirect()->route('schedule');
        return view('home');
    }
}

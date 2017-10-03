<?php

namespace App\Http\Controllers;

class ShiftController extends Controller
{
    public function clockIn()
    {
        return view('caregivers.clock_in');
    }

    public function clockOut()
    {
        return view('caregivers.clock_out');
    }

}

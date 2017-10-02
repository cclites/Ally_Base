<?php

namespace App\Http\Controllers;

class ShiftController extends Controller
{
    public function checkIn()
    {
        return view('caregivers.check_in');
    }

    public function checkOut()
    {
        return view('caregivers.check_out');
    }

}

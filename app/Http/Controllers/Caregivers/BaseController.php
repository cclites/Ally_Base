<?php


namespace App\Http\Controllers\Caregivers;


use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    /**
     * @return \App\Caregiver
     */
    protected function caregiver()
    {
        return \Auth::user()->role;
    }

}
<?php
namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    /**
     * Get the authenticated caregiver model
     *
     * @return \App\Client
     */
    protected function client()
    {
        return \Auth::user()->client;
    }

}
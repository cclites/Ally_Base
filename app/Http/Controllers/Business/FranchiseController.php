<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class FranchiseController extends Controller
{
    public function franchisees()
    {
        return view('business.franchise.index');
    }

    public function reports()
    {
        if( !Gate::allows( 'view-reports' ) ) abort( 403);

        return view('business.franchise.reports');
    }

    public function payments()
    {
        return view('business.franchise.payments');
    }

}

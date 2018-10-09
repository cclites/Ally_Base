<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FranchiseController extends Controller
{
    public function franchisees()
    {
        return view('business.franchise.index');
    }

    public function reports()
    {
        return view('business.franchise.reports');
    }

    public function payments()
    {
        return view('business.franchise.payments');
    }

}

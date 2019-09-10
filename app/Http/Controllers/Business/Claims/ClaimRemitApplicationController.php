<?php
namespace App\Http\Controllers\Business\Claims;


use App\Claims\ClaimRemit;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;

class ClaimRemitApplicationController extends BaseController
{
    public function store(ClaimRemit $claimRemit, Request $request)
    {
        dd($request->applications);
    }
}
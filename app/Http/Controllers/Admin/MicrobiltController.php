<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Services\Microbilt;

class MicrobiltController extends Controller
{
    public function index()
    {
        return view('admin.microbilt-test');
    }

    public function test()
    {
        $start = microtime(true);
        
        $mb = new Microbilt(config('services.microbilt.id'), config('services.microbilt.password'));
        $result = $mb->verifyBankAccount(request()->name, request()->account_no, request()->routing_no);

        $stop = microtime(true);

        $response = array_merge([
            'name' => request()->name,
            'account_no' => request()->account_no,
            'routing_no' => request()->routing_no,
            'time' => round($stop - $start, 3),
        ], $result);

        return new SuccessResponse('Success', $response);
    }
}

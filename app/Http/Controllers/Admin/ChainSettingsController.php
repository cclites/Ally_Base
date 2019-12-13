<?php

namespace App\Http\Controllers\Admin;

use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChainSettingsController extends Controller
{
    public function updateChain1099Settings(Chain $chain, Request $request){

        $medicaidDefault = $request->medicaid_1099_default;
        $medicaidSend = $request->medicaid_1099_send;
        $medicaidFrom = $request->medicaid_1099_from;

        $privatePayDefault = $request->private_pay_1099_default;
        $privatePaySend = $request->private_pay_1099_send;
        $privatePayFrom = $request->private_pay_1099_from;

        $otherDefault = $request->other_1099_default;
        $otherSend = $request->other_1099_send;
        $otherFrom = $request->other_1099_from;

        $data = [
            'medicaid_1099_default' => $request->medicaid_1099_default,
            'private_pay_1099_default' => $request->private_pay_1099_default,
            'other_1099_default' => $request->other_1099_default,

            'medicaid_1099_send' => $request->medicaid_1099_send,
            'private_pay_1099_send' => $request->private_pay_1099_send,
            'other_1099_send' => $request->other_1099_send,

            'medicaid_1099_from' => $request->medicaid_1099_from,
            'private_pay_1099_from' => $request->private_pay_1099_from,
            'other_1099_from' => $request->other_1099_from,
        ];

        return new SuccessResponse("Settings successfully updated");
    }

}

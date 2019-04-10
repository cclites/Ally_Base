<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PaymentHold;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentHoldController extends Controller
{
    public function update(PaymentHold $paymentHold, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'check_back_on' => 'required|date',
        ]);

        $paymentHold->update([
            'notes' => $request->notes,
            'check_back_on' => Carbon::parse($request->check_back_on)->toDateString(),
        ]);

        return new SuccessResponse("The payment hold has been updated.");
    }
}
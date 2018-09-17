<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Caregiver;
use App\Jobs\SendTextMessage;
use App\User;
use App\Responses\ErrorResponse;

class CommunicationController extends Controller
{
    /**
     * Show sms-caregivers form.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSms()
    {
        return view('business.communication.sms-caregivers');
    }

}

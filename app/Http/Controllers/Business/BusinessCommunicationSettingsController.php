<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Responses\SuccessResponse;

class BusinessCommunicationSettingsController extends Controller
{
    /**
     * Retrieve auto-sms reply settings for business or return defaults
     *
     * @param $businessId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($businessId){

        $settings  = \App\BusinessCommunications::where('business_id', $businessId)->first();

        if(!$settings){

            $settings = [
                'reply_option'=>'off',
                'week_start'=>'17:00:00',
                'week_end'=>'08:00:00',
                'weekend_start'=>'17:00:00',
                'weekend_end'=>'08:00:00',
                'message'=>''
            ];
        }

        return response()->json($settings);
    }


    /**
     * Create or update auto-sms reply settings
     *
     * @param Request $request
     * @param $businessId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $businessId){

        $settings = \App\BusinessCommunications::where('business_id', $businessId)->first();

        $messages = [
            'message.required_unless'=>'An auto reply message is required.'
        ];

        $this->validate($request, [
            'reply_option' => Rule::in(['off', 'on', 'schedule']),
            'week_start' => 'required|string|max:8',
            'week_end' => 'required|string|max:8',
            'weekend_start' => 'required|string|max:8',
            'weekend_end' => 'required|string|max:8',
            'message'=>'required_unless:reply_option,off|max:160',
        ], $messages);

        if($settings){
            $settings->reply_option = $request->reply_option;
            $settings->week_start = $request->week_start;
            $settings->week_end = $request->week_end;
            $settings->weekend_start = $request->weekend_start;
            $settings->weekend_end = $request->weekend_end;
            $settings->message = $request->message;
            $settings->save();
        }else{
            $settings = new \App\BusinessCommunications();
            $settings->reply_option = $request->reply_option;
            $settings->week_start = $request->week_start;
            $settings->week_end = $request->week_end;
            $settings->weekend_start = $request->weekend_start;
            $settings->weekend_end = $request->weekend_end;
            $settings->message = $request->message;
            $settings->business_id = $businessId;
            $settings->save();
        }

        return new SuccessResponse('Auto Reply settings updated.');

    }
}

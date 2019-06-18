<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Requests\SendTextRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Jobs\SendTextMessage;
use App\User;
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Traits\ActiveBusiness;
use App\Business;
use App\SmsThread;
use App\PhoneNumber;
use App\SmsThreadReply;
use Carbon\Carbon;
use App\SmsThreadRecipient;
use Twilio\Security\RequestValidator;

class CommunicationController extends Controller
{
    use ActiveBusiness;

    /**
     * Show text-caregivers form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function createText(Request $request)
    {
        $recipients = null;
        $message = '';

        $request->session()->reflash();

        // handle loading and setting recipient list (coming from care match results)
        if ($request->session()->has('sms.load-recipients')) {
            $recipients = Caregiver::forRequestedBusinesses()
                ->active()
                ->whereIn('caregivers.id', $request->session()->get('sms.load-recipients'))
                ->whereHas('phoneNumbers')
                ->with(['phoneNumbers', 'user'])
                ->get()
                ->map(function($caregiver) {
                    $caregiver->phone = $caregiver->smsNumber ? $caregiver->smsNumber->number : $caregiver->default_phone;
                    $caregiver->role_type = $caregiver->user->role_type;
                    return $caregiver->only(['id', 'name', 'role_type', 'phone']);
                })
                ->sortBy('name')
                ->values();
        }

        // handle draft open shift message (coming from schedule)
        // TODO: shift_id is incorrectly named, it is a schedule id
        if (request()->preset == 'open-shift' && request()->has('shift_id')) {
            $schedule = Schedule::findOrFail(request()->shift_id);
            $this->authorize('read', $schedule);
            $message = $this->draftOpenShiftMessage($schedule);
        }

        return view('business.communication.text-caregivers', compact('message', 'recipients'));
    }

    /**
     * Generate an open shift message using the supplied shift.
     *
     * @param $shift
     * @return ErrorResponse|string
     */
    public function draftOpenShiftMessage($shift)
    {
        $clientName = $shift->client->name;
        $date = $shift->starts_at->format('m/d/y');
        $time = $shift->starts_at->format('g:i A');

        $location = '';
        if ($shift->client->evvAddress) {
            $location = $shift->client->evvAddress->city . ', ' . $shift->client->evvAddress->zip;
        }
        $registryName = $shift->business->name;
        $phone = $shift->business->phone1;

        return "Shift Available\r\n$clientName / $date @ $time / $location\r\n\r\nPlease call $registryName if interested.  First come, first serve. $phone";
    }

    /**
     * Initiate SMS text blast.
     *
     * @param \App\Http\Requests\SendTextRequest $request
     * @return \Illuminate\Http\Response
     */
    public function sendText(SendTextRequest $request)
    {
        if ($request->input('all')) {
            $recipients = Caregiver::forRequestedBusinesses()
                ->active()
                ->has('phoneNumbers')
                ->with('phoneNumbers')
                ->get();
        } else {
            $recipients = Caregiver::forRequestedBusinesses()
                ->whereIn('id', $request->recipients)
                ->has('phoneNumbers')
                ->with('phoneNumbers')
                ->get();

            if ($recipients->count() == 0) {
                return new ErrorResponse(422, 'You must have at least 1 recipient.');
            }
        }

        $business = $request->getBusiness();
        $from = $business->outgoing_sms_number;
        if (empty($from)) {
            if ($request->input('can_reply')) {
                return new ErrorResponse(422, 'You cannot receive text message replies at this time because you have not been assigned a unique outgoing text messaging number, please contact Ally.');
            }

            $from = PhoneNumber::formatNational(config('services.twilio.default_number'));
        }

        $data = [
            'business_id' => $business->id,
            'from_number' => $from,
            'message' => $request->message,
            'can_reply' => $request->can_reply,
            'sent_at' => Carbon::now(),
        ];

        $this->authorize('create', [SmsThread::class, $data]);
        $thread = SmsThread::create($data);

        // send txt to caregivers default txt number
        foreach ($recipients as $recipient) {
            if ($number = $recipient->smsNumber) {
                dispatch(new SendTextMessage($number->number(false), $request->message, $business->outgoing_sms_number));
                $thread->recipients()->create(['user_id' => $recipient->id, 'number' => $number->national_number]);
            }
        }

        return new SuccessResponse('Text messages were successfully dispatched.');
    }

    /**
     * Get a list of the businesses sms threads.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function threadIndex(Request $request)
    {
        $threads = SmsThread::forRequestedBusinesses()
            ->betweenDates($request->start_date, $request->end_date)
            ->withReplies($request->reply_only == 1 ? true : false)
            ->withCount(['recipients', 'replies'])
            ->latest()
            ->get();

        if (request()->filled('json') && request()->wantsJson()) {
            return response()->json($threads);
        }

        return view('business.communication.sms-thread-list', compact('threads'));
    }

    /**
     * Get details of an individual sms thread.
     *
     * @param SmsThread $thread
     * @return \Illuminate\Http\Response
     */
    public function threadShow(SmsThread $thread)
    {
        $this->authorize('read', $thread);
        $thread->load(['recipients', 'replies']);

        $thread->unreadReplies()->update(['read_at' => Carbon::now()]);

        if (request()->wantsJson()) {
            return response()->json($thread);
        }

        return view('business.communication.sms-thread', compact(['thread']));
    }

    /**
     * Get list of SMS replies that do not belong to a thread.
     *
     * @return \Illuminate\Http\Response
     */
    public function otherReplies()
    {
        $replies = SmsThreadReply::forRequestedBusinesses()
            ->whereNull('sms_thread_id')
            ->latest()
            ->get();

        if (request()->wantsJson()) {
            return response()->json($replies);
        }

        return view('business.communication.sms-replies', compact(['replies']));
    }

    /**
     * Handles redirect to SMS Caregivers form with an
     * ID list of recipients.
     *
     * @param Request $request
     * @return SuccessResponse
     */
    public function saveRecipients(Request $request)
    {
        $request->session()->flash('sms.load-recipients', $request->ids);
        return new SuccessResponse('', null, route('business.communication.text-caregivers'));
    }

    /**
     * Retrieve auto-sms reply settings for business or return defaults
     *
     * @param $businessId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showAutoSms($businessId){

        $settings  = \App\BusinessCommunications::where('business_id', $businessId)->first();

        if(!$settings){

            $settings = [
                'auto_off'=>true,
                'on_indefinitely'=>false,
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
    public function updateAutoSms(Request $request, $businessId){

        $settings = \App\BusinessCommunications::where('business_id', $businessId)->first();

        //need to require message if auto_off=false or on_indefinitely=true
        $request->validate([
            'week_start' => 'required|string|max:8',
            'week_end' => 'required|string|max:8',
            'weekend_start' => 'required|string|max:8',
            'weekend_end' => 'required|string|max:8',
        ]);


        if($settings){
            $settings->auto_off = ($request->auto_off == 'true') ? true : false;
            $settings->on_indefinitely = ($request->on_indefinitely == 'true') ? true : false;
            $settings->week_start = $request->week_end;
            $settings->week_end = $request->week_end;
            $settings->weekend_start = $request->weekend_start;
            $settings->weekend_end = $request->weekend_end;
            $settings->message = $request->message;
        }else{
            $settings = new \App\BusinessCommunications();
            $settings->auto_off = ($request->auto_off == 'true') ? true : false;
            $settings->on_indefinitely = ($request->on_indefinitely == 'true') ? true : false;
            $settings->week_start = $request->week_end;
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

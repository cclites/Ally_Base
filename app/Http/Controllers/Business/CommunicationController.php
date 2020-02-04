<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Requests\SendTextRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Jobs\SendTextMessage;
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Traits\ActiveBusiness;
use App\SmsThread;
use App\SmsThreadReply;
use Carbon\Carbon;

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
                ->map(function ($caregiver) {
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
        $clientName = $shift->client->initialedName;
        $date = $shift->starts_at->format('m/d/y');
        $time = $shift->starts_at->format('g:i A');

        $location = '';
        if ($shift->client->evvAddress) {
            $location = ' in zip ' . $shift->client->evvAddress->zip;
        }

        return "$date@$time-Shift Available-$clientName" . $location . ". Visit the Open Shifts page anytime within Ally to express interest in this or other shifts";
    }

    /**
     * Initiate SMS text blast.
     *
     * @param \App\Http\Requests\SendTextRequest $request
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sendText(SendTextRequest $request)
    {
        $debugMode = $request->debug == 1;

        // Scrub the recipients list
        $recipients = $request->getEligibleCaregivers();
        if ($recipients->count() == 0) {
            if (filled($request->input('recipients'))) {
                return new ErrorResponse(422, 'None of the selected recipients have SMS enabled for any contact numbers.');
            }
            return new ErrorResponse(422, 'You must have at least 1 recipient.');
        }

        // Get the business selection for which outgoing number to use.
        $business = $request->getBusiness();
        if (!$from = $request->getOutgoingNumber()) {
            return new ErrorResponse(418, 'You cannot receive text message replies at this time because you have not been assigned a unique outgoing text messaging number, please contact Ally.');
        }

        // Create the SmsThread
        $data = [
            'business_id' => $business->id,
            'from_number' => $from,
            'message' => $request->message,
            'can_reply' => $request->can_reply,
            'sent_at' => Carbon::now(),
            'user_id' => auth()->user()->id,
        ];
        $this->authorize('create', [SmsThread::class, $data]);
        $thread = SmsThread::create($data);

        $failed = [];
        /** @var \App\Caregiver $recipient */
        foreach ($recipients as $recipient) {
            if ($number = $recipient->smsNumber) {
                try {
                    dispatch(new SendTextMessage($number->number(false), $request->message, $from, $debugMode));
                    $thread->recipients()->create(['user_id' => $recipient->id, 'number' => $number->national_number]);

                } catch (\Exception $ex) {
                    app('sentry')->captureException($ex);
                    $failed[] = "{$recipient->name} {$recipient->smsNumber->national_number}";
                }
            }
        }

        if( $id = $request->input( 'original_reply', false ) ){

            $reply = SmsThreadReply::find( $id );
            $reply->continued_thread_id = $thread->id;
            $reply->save();
        }

        if (count($failed) > 0) {
            return new ErrorResponse(500, "Message was sent but failed for the following users:\r\n" . join("\r\n", $failed));
        }

        if ($debugMode) {
            dd([
                'thread_data' => $data,
                'recipients' => $recipients->map(function ($item) {
                    return [
                        'user_id' => $item->id,
                        'name' => $item->name,
                        'twilio_format' => $item->smsNumber->number(false),
                        'number' => optional($item->smsNumber)->national_number,
                    ];
                }),
                'failed' => $failed,
            ]);
        }

        return new SuccessResponse('Text messages were successfully dispatched.', [ 'new_thread_id' => $thread->id ]);
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
        if ($request->filled('json') && $request->wantsJson()) {
            $threads = SmsThread::forRequestedBusinesses()
                ->betweenDates($request->start_date, $request->end_date)
                ->withReplies($request->reply_only == 1 ? true : false)
                ->withCount(['recipients', 'replies'])
                ->fullTextSearch( $request->input( 'keyword', null ) )
                ->latest()
                ->get();

            return response()->json($threads);
        }

        return view('business.communication.sms-thread-list');
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
        $thread->load(['recipients', 'replies', 'sender']);
        $thread->unreadReplies()->update(['read_at' => Carbon::now()]);

        if (request()->wantsJson()) {
            return response()->json($thread);
        }

        return view('business.communication.sms-thread', compact(['thread']));
    }

    /**
     * Get list of SMS replies that do not belong to a thread.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function otherReplies(Request $request)
    {
        if ($request->wantsJson() && filled($request->input('json'))) {
            $replies = SmsThreadReply::forRequestedBusinesses()
                ->betweenDates($request->start_date, $request->end_date)
                ->whereNull('sms_thread_id')
                ->latest()
                ->get();

            return response()->json($replies);
        }

        return view_component('business-sms-other-replies-page', 'Other Text Message Replies', [], [
            'Home' => route('home'),
            'Communication' => '',
            'Sent Texts' => route('business.communication.sms-threads')
        ]);
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

}

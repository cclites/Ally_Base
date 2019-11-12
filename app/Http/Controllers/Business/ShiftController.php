<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientAuthorization;
use App\Billing\ClientPayer;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Client;
use App\Events\UnverifiedShiftConfirmed;
use App\Http\Requests\UpdateShiftRequest;
use App\Responses\ConfirmationResponse;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Shift;
use App\ShiftConfirmation;
use App\ShiftFlag;
use App\ShiftIssue;
use App\Shifts\RateFactory;
use App\Shifts\ServiceAuthCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Arr;
use App\Events\ShiftFlagsCouldChange;
use Illuminate\Support\Collection;

class ShiftController extends BaseController
{
    public function index()
    {
        return redirect()->route('business.reports.shifts');
    }

    public function create()
    {
        $activities = $this->business()->allActivities();
        return view('business.shifts.create', compact('activities'));
    }

    /**
     * @param \App\Http\Requests\UpdateShiftRequest $request
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function store(UpdateShiftRequest $request)
    {
        $defaultStatus = Shift::WAITING_FOR_AUTHORIZATION;
        $this->authorize('create', [Shift::class, $request->getShiftArray($defaultStatus)]);

        if (!$this->validateAgainstNegativeRates($request)) {
            return new ErrorResponse(400, 'The registry fee must be a positive number.');
        }

        \DB::beginTransaction();

        if ($shift = $request->createShift($defaultStatus)) {
            $shift->activities()->sync($request->getActivities());
            $shift->syncIssues($request->getIssues());

            $duplicate = $shift->duplicatedBy;
            if ($duplicate && !$request->input('duplicate_confirm')) {
                \DB::rollBack();
                return new ConfirmationResponse('The shift may have a duplicate.', $duplicate->toArray());
            }

            \DB::commit();

            event(new ShiftFlagsCouldChange($shift));
            
            $redirect = $request->input('modal') == 1 ? null : route('business.shifts.show', [$shift->id]);
            return new SuccessResponse('You have successfully created this shift.', ['shift' => $shift->id], $redirect);
        }

        \DB::rollBack();
        return new ErrorResponse(500, 'Error creating the shift.');
    }

    public function show(Request $request, Shift $shift)
    {
        $this->authorize('read', $shift);

        // Load needed relationships
        $shift->load([
            'service', 'services', 'activities', 'issues', 'schedule',
            'client' => function ($q) { $q->select('id'); },
            'client.user' => function ($q) {
                $q->select('id', 'firstname', 'lastname');
            },
            'client.goals', 'caregiver',
            'caregiver.user' => function ($q) {
                $q->select('id', 'firstname', 'lastname');
            },
            'clientSignature', 'caregiverSignature', 'statusHistory', 'goals', 'questions', 'address'
        ]);
        $shift->append(['ally_pct', 'charged_at', 'confirmed_at']);

        // Load shift data into array before loading client info
        // TODO: move this to a resource
        // This is just a quick attempt to slim down some of the data
        // returned from the ajax call.  This is complicated because
        // the business-shift component is used in the SHR as well
        // as it's own shift view and shift duplicate pages.  Syncing
        // all 3 will require more work.
        $data = array_merge($shift->toArray(), [
            'client_name' => $shift->client->name(),
            'caregiver_name' => $shift->caregiver->name(),
            'activities' => empty($shift->activities) ? [] : $shift->activities->only(['id', 'name', 'code'])->toArray(),
            'caregiver' => [
                'name' => $shift->caregiver->name,
                'id' => $shift->caregiver->id,
            ],
            'client' => [
                'name' => $shift->client->name,
                'id' => $shift->client->id,
                'goals' => $shift->client->goals,
                'business_id' => $shift->client->business_id,
            ],
            'schedule' => [
                'notes' => optional($shift->schedule)->notes,
            ],
            'goals' => empty($shift->goals) ? [] : $shift->goals->map(function ($item) {
                return array_only($item->toArray(), ['id', 'pivot']);
            })->values()->toArray(),
        ]);

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('business.shifts.show', compact('shift'));
    }

    /**
     * @param \App\Http\Requests\UpdateShiftRequest $request
     * @param \App\Shift $shift
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        $this->authorize('update', $shift);

        // Allow admin overrides on locked shifts
        if (is_admin() && $request->input('override')) {
            $adminOverride = true;
        } else if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        if (!$this->validateAgainstNegativeRates($request)) {
            return new ErrorResponse(400, 'The registry fee must be a positive number.');
        }

        $data = $request->getShiftArray($shift->status, $shift->checked_in_method, $shift->checked_out_method);

        $allQuestions = $shift->business->questions()->forType($shift->client->client_type)->get();
        if ($allQuestions->count() > 0) {
            $fields = [];
            foreach($allQuestions as $q) {
                if ($q->required == 1) {
                    $fields['questions.' . $q->id] = 'required';
                }
            }

            $questionData = $request->validate($fields, ['questions.*' => 'Please answer all required questions.']);
        }

        if ($shift->update($data)) {
            if (isset($adminOverride)) {
                // Update persisted costs
                $shift->costs()->persist();
            }

            $shift->activities()->sync($request->getActivities());
            $shift->syncIssues($request->getIssues());
            $shift->syncGoals($request->getGoals());
            $shift->syncQuestions($allQuestions, $questionData['questions'] ?? []);
            $shift->syncServices($request->getServices());

            event(new ShiftFlagsCouldChange($shift));

            // Update flags for all shifts related to any of the service
            // authorizations that are effected by this shift.
            /** @var ClientAuthorization $auth */
            foreach ($shift->getEffectedServiceAuthorizations() as $auth) {
                $calculator = $auth->getCalculator();
                $dates = $shift->getDateSpan();
                $start = $dates[0];
                $end = count($dates) > 1 ? $dates[1] : $dates[0];
                $periods = $auth->getPeriodsForRange($start, $end);
                foreach ($periods as $period) {
                    $shifts = $calculator->getMatchingShifts($period);
                    foreach ($shifts as $shift) {
                        event(new ShiftFlagsCouldChange($shift));
                    }
                }
            }

            return new SuccessResponse('You have successfully updated this shift.');
        }
        return new ErrorResponse(500, 'The shift could not be updated.');
    }


    /**
     * @param \App\Http\Requests\UpdateShiftRequest $request
     * @return bool
     */
    protected function validateAgainstNegativeRates(UpdateShiftRequest $request)
    {
        $client = $request->getClient();
        $services = $request->getServices();

        if (count($services)) {
            foreach($services as $service) {
                if ($service['client_rate'] === null) continue;
                if ($service['caregiver_rate'] === null) {
                    return false;
                }
                if (app(RateFactory::class)->hasNegativeProviderFee($client, $service['client_rate'], $service['caregiver_rate'])) {
                    return false;
                }
            }
        } else if ($request->client_rate !== null) {
            if (app(RateFactory::class)->hasNegativeProviderFee($client, $request->client_rate, $request->caregiver_rate)) {
                return false;
            }
        }

        return true;
    }

    public function destroy(Shift $shift)
    {
        $this->authorize('delete', $shift);

        if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        \DB::beginTransaction();

        // Clean up any shift relationships.
        $shift->activities()->detach();
        $shift->goals()->detach();
        $shift->questions()->detach();
        $shift->issues()->delete();
        $shift->shiftFlags()->delete();
        $shift->statusHistory()->delete();
        $shift->services()->delete();
        $shift->expenses()->delete();

        try {
            if ($shift->delete()) {
                \DB::commit();
                return new SuccessResponse("This shift has been deleted.");
            }

        } catch (\Exception $ex) {
            // Handle foreign key exceptions (should not occur and should be handled by isReadOnly())
            app('sentry')->captureException($ex);
        }

        return new ErrorResponse(400, 'This shift could not be deleted.  Please contact Ally.');
    }

    public function confirm(Shift $shift)
    {
        $this->authorize('update', $shift);

        if ($shift->flagManager()->isDurationMismatch()) {
            return new ErrorResponse(412, 'This shift cannot be confirmed due to a time duration mismatch.');
        }

        if ($shift->statusManager()->ackConfirmation()) {
            if (!$shift->isVerified()) {
                event(new UnverifiedShiftConfirmed($shift));
            }
            return new SuccessResponse('The shift has been confirmed.', $shift->toArray());
        }

        if ($shift->statusManager()->isConfirmed()) {
            return new ErrorResponse(400, 'The shift has already been confirmed.');
        }
        return new ErrorResponse(500, 'The shift could not be confirmed due to a system error.');
    }

    public function unconfirm(Shift $shift)
    {
        $this->authorize('update', $shift);

        if (!$shift->statusManager()->isConfirmed()) {
            return new ErrorResponse(400, 'The shift is already unconfirmed.');
        }

        if ($shift->statusManager()->unconfirm()) {
            return new SuccessResponse('The shift has been unconfirmed.', $shift->toArray());
        }

        return new ErrorResponse(400, 'The shift is locked for modification.');
    }

    public function printPage(Shift $shift)
    {
        $this->authorize('read', $shift);

        // Load needed relationships
        $shift->load( 'activities', 'issues', 'schedule', 'client', 'caregiver', 'caregiverSignature', 'clientSignature', 'business' );

        $timezone = $this->business()->timezone;

        if (request()->filled('type') && strtolower(request('type')) == 'pdf') {
            // return pdf
            $pdf = PDF::loadView('business.shifts.print', compact('shift', 'timezone'));
            return $pdf->download('payment_details.pdf');
        }

        $render = 'html';
        return view('business.shifts.print', compact('shift', 'timezone', 'render'));
    }

    public function duplicate(Shift $shift)
    {
        $this->authorize('read', $shift);

        $shift->load('activities', 'services');

        // Duplicate an existing shift and advance one day
        /** @var Shift $shift */
        $shift = $shift->replicate();

        $shift->schedule_id = null;
        $shift->checked_in_time = (new Carbon($shift->checked_in_time))->addDay();
        $shift->checked_out_time = (new Carbon($shift->checked_out_time))->addDay();

        // Ensure all EVV data is cleared.
        $shift->checked_in_method = Shift::METHOD_OFFICE;
        $shift->checked_out_method = Shift::METHOD_OFFICE;
        $shift->checked_in_latitude = null;
        $shift->checked_in_longitude = null;
        $shift->checked_in_distance = null;
        $shift->checked_in_agent = null;
        $shift->checked_in_ip = null;
        $shift->checked_in_number = null;
        $shift->checked_out_latitude = null;
        $shift->checked_out_longitude = null;
        $shift->checked_out_distance = null;
        $shift->checked_out_agent = null;
        $shift->checked_out_ip = null;
        $shift->checked_out_number = null;
        $shift->checked_in_verified = null;
        $shift->checked_out_verified = null;
        $shift->verified = null;

        $activities = $shift->business->allActivities();

        return view('business.shifts.show', compact('shift', 'activities'));
    }

    /**
     * Handles manual clock out of shift for office users.
     *
     * @param Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function officeClockOut(Shift $shift)
    {
        $this->authorize('update', $shift);

        $data = request()->validate(
            [
                'checked_in_time' => 'required|date',
                'checked_out_time' => 'required|date|after_or_equal:' . request()->input('checked_in_time'),
            ],
            [
                'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.'
            ]
        );

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_method'] = Shift::METHOD_OFFICE;

        if (app('settings')->get($shift->business_id, 'auto_confirm')) {
            $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;
        }

        if ($shift->update($data)) {
            event (new ShiftFlagsCouldChange($shift));
            return new SuccessResponse('Shift was successfully clocked out.');
        }

        return new ErrorResponse(500, 'The shift could not be clocked out.');
    }

    /**
     * Fetch the client payers and rates data required
     * by the ShiftServices mixin.
     *
     * @param Client $client
     * @return \Illuminate\Http\JsonResponse
     */
    public function clientRateData(Client $client)
    {
        // unique payers
        $uniquePayers = ClientPayer::where('client_id', $client->id)
            ->with('payer')
            ->groupBy('payer_id')
            ->select('payer_id')->get()
            ->map(function(ClientPayer $clientPayer) use ($client) {
                 return [
                     'id' => $clientPayer->payer_id,
                     'name' => $clientPayer->payer_id === 0 ? $client->name : $clientPayer->payer->name,
                 ];
            });

        return response()->json([
            'payers' => $uniquePayers,
            'rates' => $client->rates()->ordered()->get(),
            'payment_type' => $client->getPaymentType(),
            'percentage_fee' => $client->getAllyPercentage(),
        ]);
    }
}

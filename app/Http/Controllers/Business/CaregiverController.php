<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Caregiver;
use App\BusinessChain;
use App\CaregiverApplication;
use App\Billing\Deposit;
use App\CaregiverAvailability;
use App\CaregiverDayOff;
use App\Document;
use App\Events\CaregiverAvailabilityChanged;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Http\Requests\CreateCaregiverRequest;
use App\Http\Requests\UpdateCaregiverAvailabilityRequest;
use App\Http\Requests\UpdateCaregiverRequest;
use App\Responses\ConfirmationResponse;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Scheduling\ScheduleAggregator;
use App\Rules\ValidSSN;
use App\Traits\Request\BankAccountRequest;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\ImageCropperUpload;
use App\Http\Requests\UpdateNotificationOptionsRequest;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Actions\CreateCaregiver;
use App\Notifications\TrainingEmail;
use App\Notifications\CaregiverWelcomeEmail;
use App\Shift;
use File;
use DB;

class CaregiverController extends BaseController
{
    use BankAccountRequest;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if (empty($request->businesses) && auth()->user()->role_type == 'admin') {
            return [];
        }

        if ($request->expectsJson()) {
            $query = Caregiver::with('businesses')
                ->forRequestedBusinesses()
                ->ordered();

            // Default to active only, unless active is provided in the query string
            if ($request->input('active', 1) !== null) {
                $query->where('active', $request->input('active', 1));
            }

            if ($request->input('status') !== null) {
                $query->where('status_alias_id', $request->input('status', null));
            }

            // Use query string ?address=1&phone_number=1 if data is needed
            if ($request->input('address')) {
                $query->with('address');
            }
            if ($request->input('phone_number')) {
                $query->with('phoneNumber');
            }

            $results = $query->get();

            return response()->json($results);
        }

        return view('business.caregivers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('business.caregivers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\CreateCaregiverRequest $request
     * @param \App\Actions\CreateCaregiver
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(CreateCaregiverRequest $request, CreateCaregiver $action)
    {
        $data = $request->filtered();

        try{
            $businessChain = $this->businessChain();
        }catch(\Exception $e){
            if (auth()->user()->role_type == 'admin') {
                $chainId = Business::where('id', $request->business_id)->pluck('chain_id')->first();
                $businessChain = BusinessChain::where('id', $chainId)->first();
            } else {
                throw $e;
            }
        }

        // Look for duplicates
        if (!$request->override) {
            if ($request->email && Caregiver::forRequestedBusinesses()->whereEmail($request->email)->first()) {
                return new ConfirmationResponse('There is already a caregiver with the email address ' . $request->email . '.');
            }
            if (Caregiver::forRequestedBusinesses()->whereName($request->firstname, $request->lastname)->first()) {
                return new ConfirmationResponse('There is already a caregiver with the name ' . $request->firstname . ' ' . $request->lastname . '.');
            }
        }


        if ($caregiver = $action->create($data, $businessChain)){
            return new CreatedResponse('The caregiver has been created.', ['id' => $caregiver->id, 'url' => route('business.caregivers.show', [$caregiver->id])]);
        }

        return new ErrorResponse(500, 'The caregiver could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Caregiver $caregiver
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function show(Caregiver $caregiver)
    {
        $this->authorize('read', $caregiver);

        $caregiver->load([
            'businesses',
            'deposits' => function ($query) {
                return $query->orderBy('created_at');
            },
            'deposits.shifts' => function ($query) {
                return $query->orderBy('checked_in_time');
            },
            'deposits.shifts.activities',
            'phoneNumbers',
            'user.documents',
            'user.notificationPreferences',
            'bankAccount',
            'availability',
            'meta',
            'skills',
            'notes.creator',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            },
            'daysOff',
        ]);
        $schedules = $caregiver->schedules()->get();
        $business = $this->business();

        // include a placeholder for the primary number if one doesn't already exist
        if ($caregiver->phoneNumbers->where('type', 'primary')->count() == 0) {
            $caregiver->phoneNumbers->prepend(['type' => 'primary', 'extension' => '', 'number' => '']);
        }

        $caregiver->future_schedules = $caregiver->futureSchedules()->count();
        $caregiver->hours_total = $caregiver->totalServiceHours();
        $caregiver->hours_last_30 = $caregiver->totalServiceHours(null, Carbon::now()->subDays(30)->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $caregiver->hours_last_90 = $caregiver->totalServiceHours(null, Carbon::now()->subDays(90)->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $caregiver->setup_url = $caregiver->setup_url;

        $notifications = $caregiver->user->getAvailableNotifications()->map(function ($cls) {
            return [
                'class' => $cls,
                'key' => $cls::getKey(),
                'title' => $cls::getTitle(),
                'disabled' => $cls::DISABLED,
            ];
        });

        return view('business.caregivers.show', compact('caregiver', 'schedules', 'business', 'notifications'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Caregiver $caregiver
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function edit(Caregiver $caregiver)
    {
        return $this->show($caregiver);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Caregiver $caregiver
     * @return ErrorResponse|SuccessResponse
     */
    public function update(UpdateCaregiverRequest $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);
        $data = $request->filtered();

        if ($request->input('no_email')) {
            $data['email'] = $caregiver->getAutoEmail();
        }

        if ($caregiver->update($data)) {
            return new SuccessResponse('The caregiver has been updated.', $caregiver, '.');
        }
        return new ErrorResponse(500, 'The caregiver could not be updated.');
    }

    /**
     * Save the caregiver's business relationships.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Caregiver $caregiver
     * @return \Illuminate\Http\Response
     */
    public function updateOfficeLocations(Request $request, Caregiver $caregiver)
    {
        $data = $request->validate([
            'businesses' => 'required|array|min:1',
            'businesses.*' => 'int|exists:businesses,id',
        ]);

        // Restrict dropping business relation if they have clients:
        $availableBusinesses = auth()->user()->role->businessChain->businesses;
        foreach ($availableBusinesses as $business) {
            if (in_array($business->id, $data['businesses'])) {
                continue;
            }

            $hasClients = $caregiver->clients()
                ->where('business_id', $business->id)
                ->exists();

            if ($hasClients) {
                return new ErrorResponse(412, "Cannot remove caregiver from the {$business->name} location because they are currently assigned to clients at that location.");
            }
        }

        $caregiver->businesses()->sync($data['businesses']);

        return new SuccessResponse('Successfully updated caregiver\'s locations');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Caregiver $caregiver
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function destroy(Caregiver $caregiver)
    {
        $this->authorize('delete', $caregiver);

        if ($caregiver->hasActiveShift()) {
            return new ErrorResponse(400, 'You cannot archive this caregiver because they have an active shift clocked in.');
        }

        if($caregiver->getUnpaidInvoicesCount() > 0){
            return new ErrorResponse(400, 'Warning: This caregiver has an outstanding invoice or payment and cannot be deactivated. Contact Ally support with any questions.');
        }

        try {
            $inactive_at = request('inactive_at') ? Carbon::parse(request('inactive_at')) : Carbon::now();
        } catch (\Exception $ex) {
            return new ErrorResponse(422, 'Invalid inactive date.');
        }

        \DB::beginTransaction();

        $data = [
            'active' => false,
            'inactive_at' => $inactive_at,
            'deactivation_reason_id' => request('deactivation_reason_id'),
            'deactivated_by' => \Auth::user()->name,
            'deactivation_note' => request('note'),
            'status_alias_id' => null,
        ];

        if ($caregiver->update($data)) {

            $caregiver->unassignFromFutureSchedules();
            $caregiver->removeOutstandingScheduleRequests();

            \DB::commit();
            return new SuccessResponse('The caregiver has been archived.', [], route('business.caregivers.index'));
        }

        return new ErrorResponse(500, 'Error archiving this caregiver.  Please try again.');
    }

    /**
     * Re-activate an archived (inactive) caregiver.  This reverses the destroy action above.
     *
     * @param \App\Caregiver $caregiver
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function reactivate(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        if ($caregiver->update(['active' => true, 'inactive_at' => null, 'status_alias_id' => null])) {
            return new SuccessResponse('The caregiver has been re-activated.', null, '.');
        }
        return new ErrorResponse(500, 'Could not re-activate the selected caregiver.');
    }

    public function address(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);
        $this->authorize('update', $caregiver);

        return (new AddressController())->update($request, $caregiver->user, $type, 'The caregiver\'s address');
    }

    public function phone(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);
        $this->authorize('update', $caregiver);

        return (new PhoneController())->upsert($request, $caregiver->user, $type, 'The caregiver\'s phone number');
    }

    public function schedule(Request $request, ScheduleAggregator $aggregator, $caregiver_id)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);
        $this->authorize('update', $caregiver);

        $aggregator->where('caregiver_id', $caregiver->id);

        $start = new Carbon(
            $request->input('start', date('Y-m-d', strtotime('First day of this month'))),
            $caregiver->businesses->first()->timezone ?? 'America/New_York'
        );
        $end = new Carbon(
            $request->input('end', date('Y-m-d', strtotime('First day of next month'))),
            $caregiver->businesses->first()->timezone ?? 'America/New_York'
        );

        $events = new ScheduleEventsResponse($aggregator->getSchedulesBetween($start, $end));
        return $events;
    }

    public function bankAccount(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $existing = $caregiver->bankAccount;
        $account = $this->validateBankAccount($request, $existing);
        if ($caregiver->setBankAccount($account)) {
            return new SuccessResponse('The bank account has been saved.');
        }
        return new ErrorResponse(500, 'The bank account could not be saved.');
    }

    public function changePassword(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        if ($caregiver->user->changePassword($request->input('password'))) {
            return new SuccessResponse('The caregiver\'s password has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update caregiver password.');
    }

    public function misc(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $data = $request->validate(['misc' => 'nullable|string']);
        $caregiver->update($data);
        return new SuccessResponse('Caregiver updated');
    }

    /**
     * Update the caregiver availability tab.
     *
     * @param UpdateCaregiverAvailabilityRequest $request
     * @param Caregiver $caregiver
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function preferences(UpdateCaregiverAvailabilityRequest $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        //This call looks at scheduled vacation days saved in the system and compares
        //them against scheduled vacation days to find the difference.
        //$diffDaysOff represents those days.
        $diffDaysOff = CaregiverDayOff::arrayDiffCustom($request->daysOffData(), $caregiver->daysOff);

        \Log::info("DIFF DAYS OFF");
        \Log::info($diffDaysOff);

        //This call looks at CG available days saved in the system and compares against
        //available days stored in the system. If any days were previously marked available
        //and are now unavailable, $diffAvailability represents those days.
        $diffAvailability = CaregiverAvailability::arrayDiffAvailability($request->availabilityData(), $caregiver->availability);

        \Log::info("DIFF AVAILABILITY.");
        \Log::info($$diffAvailability);

        $vacationConflict = $availabilityConflict = [];

        if($diffDaysOff){
            $vacationConflict = CaregiverDayOff::checkAddedVacationConflict($caregiver->id, $diffDaysOff);
        }

        if($diffAvailability){
            $availabilityConflict = CaregiverAvailability::checkRemovedAvailableDaysConflict($caregiver->id, $diffAvailability);
        }

        if( $vacationConflict || $availabilityConflict){

            if( \Auth::user()->role_type === 'office_user' ){
                return response()->json(['error'=> 'caregiver has conflict']);
            }

        }

        \DB::beginTransaction();

        $caregiver->update(['preferences' => $request->preferencesData()]);
        $caregiver->setAvailability($request->availabilityData());

        if($diffDaysOff){
            $caregiver->daysOff()->delete();
            $caregiver->daysoff()->createMany($request->daysOffData());
        }

        \DB::commit();

        return new SuccessResponse('Caregiver availability preferences have been saved.', $caregiver->fresh()->daysOff);
    }

    public function skills(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $request->validate([
            'skills' => 'array',
            'skills.*' => 'integer',
        ]);

        $caregiver->skills()->sync($request->skills);
        return new SuccessResponse('Caregiver skills updated');
    }

    public function defaultRates(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $data = $request->validate([
            'hourly_rate_id' => 'nullable|exists:rate_codes,id',
            'fixed_rate_id' => 'nullable|exists:rate_codes,id',
        ]);

        $caregiver->update($data);
        return new SuccessResponse('The default rates have been saved.');
    }

    /**
     * Update caregiver's user notification settings.
     *
     * @param UpdateNotificationOptionsRequest $request
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateNotificationOptions(UpdateNotificationOptionsRequest $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $data = $request->validated();

        if ($caregiver->user->update($data)) {
            return new SuccessResponse('Caregiver\'s notification options have been updated.');
        }

        return new ErrorResponse(500, 'Unexpected error updating the Caregiver\'s notification options.  Please try again.');
    }

    /**
     * Update caregiver's user notification preferences.
     *
     * @param UpdateNotificationPreferencesRequest $request
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateNotificationPreferences(UpdateNotificationPreferencesRequest $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $caregiver->user->syncNotificationPreferences($request->validated());

        return new SuccessResponse('Caregiver\'s notification preferences have been saved.');
    }

    /**
     * Send welcome email to the caregiver.
     *
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\Response
     */
    public function welcomeEmail(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $caregiver->update(['welcome_email_sent_at' => Carbon::now()]);

        $caregiver->notify(new CaregiverWelcomeEmail($caregiver, $this->businessChain()));

        // Use the reload page redirect to update the welcome_emaiL_sent_at timestamp
        return new SuccessResponse('A welcome email was dispatched to the Caregiver.', null, '.');
    }

    /**
     * Send training email to the caregiver.
     *
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\Response
     */
    public function trainingEmail(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $caregiver->update(['training_email_sent_at' => Carbon::now()]);

        $caregiver->notify(new TrainingEmail());

        // Use the reload page redirect to update the timestamp
        return new SuccessResponse('A training email was dispatched to the Caregiver.', null, '.');
    }

    /**
     * 
     * generate a discharge letter for the caregiver resource ON THE FLY
     */
    public function dischargeLetter( Caregiver $caregiver )
    {
        $caregiver->load( 'deactivationReason' );

        $query = \DB::table('shifts')->where('caregiver_id', $caregiver->id);
        $totalLifetimeShifts = $query->count();
        $totalLifetimeHours = $query->selectRaw('SUM(hours) as hours')->first()->hours;

        $pdf = PDF::loadView( 'business.caregivers.deactivation_reason', [

            'caregiver'           => $caregiver,
            'deactivatedBy'       => $caregiver->user->deactivated_by ?? 'Unknown',
            'totalLifetimeHours'  => $totalLifetimeHours,
            'totalLifetimeShifts' => $totalLifetimeShifts,
            'override_ally_logo' => optional($caregiver->businesses->first())->logo,
        ]);

        $filePath = $caregiver->id . '-' . 'deactivation-details-' . Carbon::now()->format('m-d-Y');
        return $pdf->stream( $filePath . '.pdf' );
    }

    /**
     * Check if the Caregiver has open (unpaid) invoices.
     *
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\JsonResponse
     */
    public function openInvoices(Caregiver $caregiver)
    {
        $count = $caregiver->getUnpaidInvoicesCount();

        return response()->json([
            'caregiver_id' => $caregiver->id,
            'open_invoice_count' => $caregiver->getUnpaidInvoicesCount(),
            'has_open_invoices' => $count > 0
        ]);
    }
}

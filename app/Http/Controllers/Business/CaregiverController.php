<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\CaregiverApplication;
use App\Deposit;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Responses\ConfirmationResponse;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Scheduling\ScheduleAggregator;
use App\Rules\ValidSSN;
use App\Traits\Request\BankAccountRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $caregivers = $this->business()->caregivers()
            ->when($request->filled('active') || $request->expectsJson(), function($query) use ($request) {
                $query->where('active', $request->input('active', 1));
            })
            ->orderByName()
            ->with(['user', 'addresses', 'phoneNumbers'])
            ->get();

        if ($request->expectsJson()) {
            return $caregivers;
        }

        $multiLocation = [
            'multiLocationRegistry' => $this->business()->multi_location_registry,
            'name' => $this->business()->name
        ];

        return view('business.caregivers.index', compact('caregivers', 'multiLocation'));
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => 'required|unique:users',
            'date_of_birth' => 'nullable',
            'ssn' => 'nullable',
            'password' => 'nullable|confirmed',
            'title' => 'required',
            'medicaid_id' => 'nullable',
        ]);

        // Look for duplicates in the current business
        if (!$request->override && $duplicate = $this->business()->checkForDuplicateUser($request->firstname, $request->lastname, $request->email, 'caregiver')) {
            if ($duplicate == 'email') {
                return new ConfirmationResponse('There is already a caregiver with the email address ' . $request->email . '.');
            }
            return new ConfirmationResponse('There is already a caregiver with the name ' . $request->firstname . ' ' . $request->lastname . '.');
        }

        // Format data for insertion
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        $data['password'] = bcrypt($data['password'] ?? str_random());


        $caregiver = new Caregiver($data);
        if ($request->input('no_email')) {
            $caregiver->setAutoEmail();
        }
        if ($this->business()->caregivers()->save($caregiver)) {
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
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $caregiver->load([
            'deposits' => function ($query) {
                return $query->orderBy('created_at');
            },
            'deposits.shifts' => function ($query) {
                return $query->orderBy('checked_in_time');
            },
            'deposits.shifts.activities',
            'phoneNumbers',
            'user.documents',
            'bankAccount',
            'notes.creator',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            }
        ]);
        $caregiver->masked_ssn = '***-**-' . substr($caregiver->ssn, -4);
        $schedules = $caregiver->schedules()->get();
        $business = $this->business()->load(['clients', 'caregivers']);

        // include a placeholder for the primary number if one doesn't already exist
        if ($caregiver->phoneNumbers->where('type', 'primary')->count() == 0) {
            $caregiver->phoneNumbers->prepend(['type' => 'primary', 'extension' => '', 'number' => '']);
        }

        $caregiver->future_schedules = $caregiver->futureSchedules()->count();

        return view('business.caregivers.show', compact('caregiver', 'schedules', 'business'));
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
    public function update(Request $request, Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => ['required', Rule::unique('users')->ignore($caregiver->id)],
            'date_of_birth' => 'nullable|date',
            'title' => 'required',
            'misc' => 'nullable|string',
            'medicaid_id' => 'nullable',
        ];

        if ($request->filled('ssn') && !str_contains($request->ssn, '*')) {
            $rules += [
                'ssn' => new ValidSSN()
            ];
        }

        $data = $request->validate($rules);

        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);

        if ($request->input('no_email')) {
            $data['email'] = $caregiver->getAutoEmail();
        }

        if ($caregiver->update($data)) {
            return new SuccessResponse('The caregiver has been updated.');
        }
        return new ErrorResponse(500, 'The caregiver could not be updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Caregiver $caregiver
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function destroy(ScheduleAggregator $aggregator, Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        if ($caregiver->hasActiveShift()) {
            return new ErrorResponse(400, 'You cannot archive this caregiver because they have an active shift clocked in.');
        }

        try {
            $inactive_at = request('inactive_at') ? Carbon::parse(request('inactive_at')) : Carbon::now();
        } catch (\Exception $ex) {
            return new ErrorResponse(422, 'Invalid inactive date.');
        }

        if ($caregiver->update(['active' => false, 'inactive_at' => $inactive_at])) {
            $caregiver->unassignFromFutureSchedules();
            return new SuccessResponse('The caregiver has been archived.', [], route('business.caregivers.index'));
        }
        return new ErrorResponse(500, 'Error archiving this caregiver.');
    }

    /**
     * Re-activate an archived (inactive) caregiver.  This reverses the destroy action above.
     *
     * @param \App\Caregiver $caregiver
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function reactivate(Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        if ($caregiver->update(['active' => true, 'inactive_at' => null])) {
            return new SuccessResponse('The caregiver has been re-activated.');
        }
        return new ErrorResponse('Could not re-activate the selected caregiver.');
    }

    public function address(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        return (new AddressController())->update($request, $caregiver->user, $type, 'The caregiver\'s address');
    }

    public function phone(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        return (new PhoneController())->upsert($request, $caregiver->user, $type, 'The caregiver\'s phone number');
    }

    public function schedule(Request $request, ScheduleAggregator $aggregator, $caregiver_id)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

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

    public function sendConfirmationEmail($caregiver_id)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);
        $caregiver->sendConfirmationEmail();
        return new SuccessResponse('Email Sent to Caregiver');
    }

    public function bankAccount(Request $request, Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $existing = $caregiver->bankAccount;
        $account = $this->validateBankAccount($request, $existing);
        if ($caregiver->setBankAccount($account)) {
            return new SuccessResponse('The bank account has been saved.');
        }
        return new ErrorResponse(500, 'The bank account could not be saved.');
    }

    public function changePassword(Request $request, Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

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
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }
        $data = $request->validate(['misc' => 'required|string']);
        $caregiver->update($data);
        return new SuccessResponse('Caregiver updated');
    }

    public function preferences(Request $request, Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }
        $data = $request->validate(['preferences' => 'required|string']);
        $caregiver->update($data);
        return new SuccessResponse('Caregiver updated');
    }
}

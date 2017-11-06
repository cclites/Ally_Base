<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Traits\Request\PaymentMethodUpdate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CaregiverController extends BaseController
{
    use PaymentMethodUpdate;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $caregivers = $this->business()->caregivers()->with(['user', 'addresses', 'phoneNumbers'])->get();
        return view('business.caregivers.index', compact('caregivers'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|email',
            'username' => 'required|unique:users',
            'date_of_birth' => 'nullable',
            'ssn' => 'nullable',
            'password' => 'required|confirmed',
            'title' => 'required',
        ]);

        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        $data['password'] = bcrypt($data['password']);


        $caregiver = new Caregiver($data);
        if ($request->input('no_email')) {
            $caregiver->setAutoEmail();
        }
        if ($this->business()->caregivers()->save($caregiver)) {
            return new CreatedResponse('The caregiver has been created.', ['id' => $caregiver->id], route('business.caregivers.show', [$caregiver->id]));
        }

        return new ErrorResponse(500, 'The caregiver could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function show(Caregiver $caregiver)
    {
        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
    }

//        $caregiver->load(['user', 'addresses', 'phoneNumbers', 'user.documents', 'bankAccount']);
        $caregiver->load(['user.documents', 'bankAccount']);
        $schedules = $caregiver->schedules()->get();

        return view('business.caregivers.show', compact('caregiver', 'schedules'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function edit(Caregiver $caregiver)
    {
        return $this->show($caregiver);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Caregiver $caregiver)
    {
        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|email',
            'username' => ['required', Rule::unique('users')->ignore($caregiver->id)],
            'date_of_birth' => 'nullable|date',
            'title' => 'required',
        ]);

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
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Caregiver $caregiver)
    {
        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $events = $caregiver->getEvents(Carbon::now(), new Carbon('2100-01-01'));
        if (count($events)) {
            $event = current($events);
            return new ErrorResponse(400, 'This caregiver still has active schedules.  Their next client is ' . $event['title'] . '.');
        }

        if ($caregiver->delete()) {
            return new SuccessResponse('The caregiver has been archived.', [], route('business.caregivers.index'));
        }
        return new ErrorResponse(500, 'Error archiving this caregiver.');
    }

    public function address(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        return (new AddressController())->update($request, $caregiver->user, $type, 'The caregiver\'s address');
    }

    public function phone(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        return (new PhoneController())->update($request, $caregiver->user, $type, 'The caregiver\'s phone number');
    }

    public function schedule(Request $request, $caregiver_id)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $start = $request->input('start', date('Y-m-d', strtotime('First day of last month -2 months')));
        $end = $request->input('end', date('Y-m-d', strtotime('First day of this month +13 months')));

        if (strlen($start) > 10) $start = substr($start, 0, 10);
        if (strlen($end) > 10) $end = substr($end, 0, 10);

        $events = new ScheduleEventsResponse($caregiver->getEvents($start, $end));
        return $events;

    }

    public function bankAccount(Request $request, $caregiver_id)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $existing = $caregiver->bankAccount;
        $account = $this->updateBankAccount($request, $caregiver, $existing);
        if ($account) {
            if (!$existing) $caregiver->update(['bank_account_id' => $account->id]);
            return new SuccessResponse('The bank account has been saved.');
        }
        return new ErrorResponse(500, 'The bank account could not be saved.');
    }
}

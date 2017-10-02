<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Scheduling\ScheduleAggregator;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use Illuminate\Http\Request;

class CaregiverController extends BaseController
{
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
            'email' => 'required|email',
            'date_of_birth' => 'nullable',
            'ssn' => 'nullable',
            'password' => 'required|confirmed',
        ]);

        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        $data['password'] = bcrypt($data['password']);

        $caregiver = new Caregiver($data);
        if ($this->business()->caregivers()->save($caregiver)) {
            return new CreatedResponse('The caregiver has been created.', ['id' => $caregiver->id]);
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

//        $caregiver->load(['user', 'addresses', 'phoneNumbers']);
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Caregiver $caregiver)
    {
        //
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

        $aggregator = new ScheduleAggregator();
        foreach($caregiver->schedules as $schedule) {
            $title = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $aggregator->add($title, $schedule);
        }

        $start = $request->input('start', date('Y-m-d', strtotime('First day of last month -2 months')));
        $end = $request->input('end', date('Y-m-d', strtotime('First day of this month +13 months')));

        if (strlen($start) > 10) $start = substr($start, 0, 10);
        if (strlen($end) > 10) $end = substr($end, 0, 10);

        $events = new ScheduleEventsResponse($aggregator->events($start, $end));
        return $events;

    }
}

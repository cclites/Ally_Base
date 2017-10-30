<?php

namespace App\Http\Controllers;

use App\Business;
use App\CaregiverApplication;
use App\CaregiverPosition;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CaregiverApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Business $business
     * @return \Illuminate\Http\Response
     */
    public function create(Business $business)
    {
        $positions = CaregiverPosition::all();
        return view('caregivers.applications.create', compact('business', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'cell_phone' => 'required'
        ]);

        $data = $request->all();

        $data['preferred_days'] = implode(',', $data['preferred_days']);
        $data['preferred_times'] = implode(',', $data['preferred_times']);
        $data['preferred_shift_length'] = implode(',', $data['preferred_shift_length']);
        $data['heard_about'] = implode(',', $data['heard_about']);
        $data['date_of_birth'] = Carbon::parse($data['date_of_birth']);

        $data['preferred_start_date'] = Carbon::parse($data['preferred_start_date']);
        $data['employer_1_approx_start_date'] = Carbon::parse($data['employer_1_approx_start_date']);
        $data['employer_1_approx_end_date'] = Carbon::parse($data['employer_1_approx_end_date']);
        $data['employer_2_approx_start_date'] = Carbon::parse($data['employer_2_approx_start_date']);
        $data['employer_2_approx_end_date'] = Carbon::parse($data['employer_2_approx_end_date']);
        $data['employer_3_approx_start_date'] = Carbon::parse($data['employer_3_approx_start_date']);
        $data['employer_3_approx_end_date'] = Carbon::parse($data['employer_3_approx_end_date']);

        $application = CaregiverApplication::create($data);

        if ($application) {
            return new CreatedResponse('Application submitted successfully.', [], '/');
        }
        return new ErrorResponse(500, 'The application could not be submitted.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CaregiverApplication  $caregiverApplication
     * @return \Illuminate\Http\Response
     */
    public function show(CaregiverApplication $caregiverApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CaregiverApplication  $caregiverApplication
     * @return \Illuminate\Http\Response
     */
    public function edit(CaregiverApplication $caregiverApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CaregiverApplication  $caregiverApplication
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CaregiverApplication $caregiverApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CaregiverApplication  $caregiverApplication
     * @return \Illuminate\Http\Response
     */
    public function destroy(CaregiverApplication $caregiverApplication)
    {
        //
    }
}

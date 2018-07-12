<?php

namespace App\Http\Controllers;

use App\Business;
use App\CaregiverApplication;
use App\CaregiverApplicationStatus;
use App\CaregiverPosition;
use App\Http\Requests\CaregiverApplicationStoreRequest;
use App\Http\Requests\CaregiverApplicationUpdateRequest;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
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
        $user = OfficeUser::find(auth()->id());
        $business = $user->businesses()->first();
        $applications = CaregiverApplication::with('position', 'status')->where('business_id', $business->id)->get();
        $statuses = CaregiverApplicationStatus::all();
        $positions = CaregiverPosition::all();
        return view('caregivers.applications.index', compact('business', 'applications', 'statuses', 'positions'));
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
     * @param  CaregiverApplicationStoreRequest $request
     * @return CreatedResponse|ErrorResponse
     */
    public function store(CaregiverApplicationStoreRequest $request)
    {
        $data = $request->filtered();
        $application = CaregiverApplication::create($data);

        if ($application) {
            return new CreatedResponse('Application submitted successfully.', [], '/business/caregivers/applications');
        }
        return new ErrorResponse(500, 'The application could not be submitted.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $application = CaregiverApplication::with('position', 'status')->find($id);
        return view('caregivers.applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = OfficeUser::find(auth()->id());
        $business = $user->businesses()->first();
        $application = CaregiverApplication::find($id);
        $application->preferred_days = explode(',', $application->preferred_days);
        $application->preferred_times = explode(',', $application->preferred_times);
        $application->preferred_shift_length = explode(',', $application->preferred_shift_length);
        $application->heard_about = explode(',', $application->heard_about);
        $positions = CaregiverPosition::all();
        return view('caregivers.applications.edit', compact('application', 'business', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\CaregiverApplicationUpdateRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CaregiverApplicationUpdateRequest $request, $id)
    {
        $data = $request->filtered();
        $application = CaregiverApplication::find($id);

        $application->update($data);

        return new SuccessResponse('Application Updated');

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

    /**
     * Filter the list of Caregiver Applications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $user = OfficeUser::find(auth()->id());
        $business = Business::find($user->businesses()->first()->id);
        $applications = CaregiverApplication::with('position', 'status')
            ->where('business_id', $business->id)
            ->when($request->filled('from_date'), function ($query) use ($request) {
                return $query->where('created_at', '>=', Carbon::parse($request->from_date));
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                return $query->where('created_at', '<=', Carbon::parse($request->to_date)->addDay());
            })
            ->when($request->filled('position'), function ($query) use ($request) {
                return $query->where('caregiver_position_id', $request->position);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('caregiver_application_status_id', $request->status);
            })
            ->get();
        return response()->json($applications);
    }
}

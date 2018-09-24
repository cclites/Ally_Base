<?php

namespace App\Http\Controllers;

use App\Address;
use App\Business;
use App\Caregiver;
use App\CaregiverApplication;
use App\CaregiverApplicationStatus;
use App\CaregiverPosition;
use App\Http\Requests\CaregiverApplicationStoreRequest;
use App\Http\Requests\CaregiverApplicationUpdateRequest;
use App\PhoneNumber;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\ActiveBusiness;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CaregiverApplicationController extends Controller
{
    use ActiveBusiness;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index()
    {
        $applications = CaregiverApplication::where('business_id', $this->business()->id)->get();
        return view('caregivers.applications.index', compact('business', 'applications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Business $business
     * @return \Illuminate\Http\Response
     */
    public function create(Business $business)
    {
        return view('caregivers.applications.create', compact('business'));
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
            return new CreatedResponse('Application submitted successfully.', [], route('applications.done', [$request->business_id, $application]));
        }
        return new ErrorResponse(500, 'The application could not be submitted.');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\CaregiverApplication $application
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function show(CaregiverApplication $application)
    {
        if ($application->business_id != $this->business()->id) {
            abort(403);
        }

        $this->updateStatus($application);

        return view('caregivers.applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\CaregiverApplication $application
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function edit(CaregiverApplication $application)
    {
        if ($application->business_id != $this->business()->id) {
            abort(403);
        }

        $this->updateStatus($application);

        $business = $this->business();
        $application->preferred_days = explode(',', $application->preferred_days);
        $application->preferred_times = explode(',', $application->preferred_times);
        $application->preferred_shift_length = explode(',', $application->preferred_shift_length);
        $application->heard_about = explode(',', $application->heard_about);
        return view('caregivers.applications.edit', compact('application', 'business'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\CaregiverApplicationUpdateRequest $request
     * @param \App\CaregiverApplication $application
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(CaregiverApplicationUpdateRequest $request, CaregiverApplication $application)
    {
        if ($application->business_id != $this->business()->id) {
            abort(403);
        }

        $data = $request->filtered();
        $application->update($data);

        return new SuccessResponse('Application Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CaregiverApplication $application
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(CaregiverApplication $application)
    {
        abort(404); // not implemented
        if ($application->business_id != $this->business()->id) {
            abort(403);
        }

        //
    }

    /**
     * Filter the list of Caregiver Applications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $applications = CaregiverApplication::where('business_id', $this->business()->id)
            ->when($request->filled('from_date'), function ($query) use ($request) {
                return $query->where('created_at', '>=', Carbon::parse($request->from_date));
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                return $query->where('created_at', '<=', Carbon::parse($request->to_date)->addDay());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->get();
        return response()->json($applications);
    }

    /**
     * Show a completed page once an application is created
     *
     * @param \App\Business $business
     * @param \App\CaregiverApplication $application
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function done(Business $business, CaregiverApplication $application)
    {
        return view('caregivers.applications.done', compact('business', 'application'));
    }

    /**
     * Convert an application into a caregiver
     *
     * @param \App\CaregiverApplication $application
     * @return \App\Responses\CreatedResponse|\App\Responses\ErrorResponse
     * @throws \Exception
     */
    public function convert(CaregiverApplication $application)
    {
        if ($application->business_id != $this->business()->id) {
            abort(403);
        }

        if ($application->status === CaregiverApplication::STATUS_CONVERTED) {
            return new ErrorResponse(409, 'This application has already been converted.');
        }

        if ($caregiver = $application->convertToCaregiver()) {
            $this->updateStatus($application, CaregiverApplication::STATUS_CONVERTED);
            return new CreatedResponse('The application has been converted into an active caregiver.', null, route('business.caregivers.show', [$caregiver]));
        }
    }

    protected function updateStatus(CaregiverApplication $application, $status = CaregiverApplication::STATUS_OPEN)
    {
        if ($status !== CaregiverApplication::STATUS_CONVERTED) {
            $application->update(['status' => $status]);
        }
    }
}

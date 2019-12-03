<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessChain;
use App\CaregiverApplication;
use App\Http\Requests\UpdateCaregiverApplicationRequest;
use App\Signature;
use App\Http\Controllers\Business\BaseController as BusinessBaseController;
use App\Http\Requests\CaregiverApplicationStoreRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Notifications\Business\ApplicationSubmitted;

class CaregiverApplicationController extends BusinessBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $query = $this->businessChain()
            ->caregiverApplications()
            ->ordered();

        if ($request->expectsJson()) {
            $timezone = $this->businessChain()->businesses()->first()->timezone ?? 'America/New_York';

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
            if ($startDate = $request->input('start_date')) {
                $query->where('created_at', '>=', Carbon::parse($startDate, $timezone)->setTime(0,0,0));
            }
            if ($endDate = $request->input('end_date')) {
                $query->where('created_at', '<', Carbon::parse($endDate, $timezone)->addDay());
            }

            return $query->whereArchived($request->archived)->get();
        }

        $applications = $query->whereStatus('Open')->whereArchived(0)->get();
        $applicationUrl = $this->businessChain()->getCaregiverApplicationUrl();
        return view('caregivers.applications.index', compact('applicationUrl', 'applications'));
    }

    /**
     * Backwards compatibility redirect
     *
     * @param \App\Business $business
     * @return \Illuminate\Http\RedirectResponse
     */
    public function oldRedirect(Business $business)
    {
        return redirect($business->chain->getCaregiverApplicationUrl());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function create($slug)
    {
        $businessChain = BusinessChain::whereSlug($slug)->firstOrFail();

        return view('caregivers.applications.create', compact('businessChain'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CaregiverApplicationStoreRequest $request
     * @param string $slug
     * @return CreatedResponse|ErrorResponse
     */
    public function store(CaregiverApplicationStoreRequest $request, $slug)
    {
        $businessChain = BusinessChain::whereSlug($slug)->firstOrFail();
        $data = $request->filtered();

        //Throw signature into another variable and unset it from $data so
        //application saves correctly.
        $signature = $data['caregiver_signature'];
        unset($data['caregiver_signature']);

        $application = $businessChain->caregiverApplications()->create($data);

        if ($application)
        {
            Signature::attachToModel($application, $signature, 'caregiver' );

            \Notification::send($businessChain->notifiableUsers(), new ApplicationSubmitted($application));

            return new CreatedResponse('Application submitted successfully.', [], route('business_chain_routes.applications.done', ['slug' => $slug, 'application' => $application]));
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
        $this->authorize('read', $application);
        $application->updateStatus();
        $application->caregiver_signature = $application->signature;

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
        $this->authorize('update', $application);
        $application->updateStatus();

        $business = $this->business();
        $application->preferred_days = explode(',', $application->preferred_days);
        $application->preferred_times = explode(',', $application->preferred_times);
        $application->preferred_shift_length = explode(',', $application->preferred_shift_length);
        $application->heard_about = explode(',', $application->heard_about);
        $application->caregiver_signature = $application->signature;

        return view('caregivers.applications.edit', compact('application', 'business'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCaregiverApplicationRequest $request
     * @param \App\CaregiverApplication $application
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateCaregiverApplicationRequest $request, CaregiverApplication $application)
    {
        $this->authorize('update', $application);

        Signature::attachToModel($application, request('caregiver_signature'), 'caregiver' );

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
        $this->authorize('delete', $application);

        if( $application->delete() ){
            return new SuccessResponse('Application Deleted');
        }

        return new ErrorResponse(500, 'Unable to delete application');

    }

    public function archive(CaregiverApplication $application)
    {
        $this->authorize('update', $application);

        if( $application->update(['archived'=>true]) ){
            return new SuccessResponse('Application has been archived');
        }

        return new ErrorResponse(500, 'Unable to archive application');
    }

    /**
     * Show a completed page once an application is created
     *
     * @param string $slug
     * @param \App\CaregiverApplication $application
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function done($slug, CaregiverApplication $application)
    {
        $businessChain = BusinessChain::whereSlug($slug)->firstOrFail();

        return view('caregivers.applications.done', compact('businessChain', 'application'));
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
        $this->authorize('update', $application);

        if ($application->status === CaregiverApplication::STATUS_CONVERTED) {
            return new ErrorResponse(409, 'This application has already been converted.');
        }

        try {

            if ( $caregiver = $application->convertToCaregiver() ) {

                return new CreatedResponse('The application has been converted into an active caregiver.', null, route('business.caregivers.show', [$caregiver]));
            }

            return new ErrorResponse( 500, 'An unexpected error occurred converting this application.' );

        } catch( NumberParseException $e ){
            return new ErrorResponse( 500, 'There was an issue with an invalid phone number on the application.  Please update the application and try again.' );
        }
    }
}

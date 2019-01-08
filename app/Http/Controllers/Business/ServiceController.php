<?php
namespace App\Http\Controllers\Business;

use Auth;
use App\Billing\Service;
use App\Http\Requests\CreateServiceRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;

class ServiceController extends BaseController
{   
    /**
     * Display a list of services
     */
    public function index()
    {
        $services = Service::where('chain_id', Auth::user()->officeUser->chain_id)->get();
        
        return view('business.service', compact('services'));
    }

    /**
     * Store a newly created service in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateServiceRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [Service::class, $data]);

        if ($service = Service::create($request->filtered())) {
            return new CreatedResponse('New Service has been created', $service);
        }
        
        return new ErrorResponse(500, 'The service could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Billing\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(CreateServiceRequest $request, Service $service)
    {
        $this->authorize('update', $service);

        if ($service->update($request->filtered())) {
            return new SuccessResponse('Service has been updated.', $service);
        }

        return new ErrorResponse(500, 'The service could not be updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Billing\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        try {
            if ($service->delete()) {
                return new SuccessResponse('Service has been deleted.');
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }

        return new ErrorResponse(500, 'The service could not be deleted.');
    }
}

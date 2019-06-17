<?php
namespace App\Http\Controllers\Business;

use App\Billing\Service;
use App\Http\Requests\CreateServiceRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ServiceController extends BaseController
{   
    /**
     * Display a list of services
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Service::forAuthorizedChain()->ordered();
        $services = $query->get();
        
        if ($request->wantsJson() && $request->json) {
            return response()->json($services);
        }

        return view('business.service', compact('services'));
    }

    /**
     * Store a newly created service in storage.
     *
     * @param  \App\Http\Requests\CreateServiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateServiceRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [Service::class, $data]);

        if ($service = Service::create($request->filtered())) {
            if ($request->input('default')) {
                Service::setDefault($service->chain_id, $service);
            }
            return new CreatedResponse('New Service has been created', $service);
        }
        
        return new ErrorResponse(500, 'The service could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CreateServiceRequest  $request
     * @param  \App\Billing\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(CreateServiceRequest $request, Service $service)
    {
        $this->authorize('update', $service);

        if ($service->update($request->filtered())) {
            if ($request->input('default')) {
                Service::setDefault($service->chain_id, $service);
            }

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

        if ($service->default) {
            return new ErrorResponse(400, 'You cannot delete the default billing service.');
        }

        try {
            if ($service->delete()) {
                return new SuccessResponse('Service has been deleted.');
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }

        return new ErrorResponse(500, 'The service could not be deleted due to existing shifts or service authorizations using this code.');
    }
}

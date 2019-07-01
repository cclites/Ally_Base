<?php
namespace App\Http\Controllers\Business;

use Auth;
use App\Client;
use App\Billing\ClientAuthorization;
use App\Http\Requests\CreateClientAuthRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Log;
class ClientAuthController extends BaseController
{
    /**
     * Display a list of authorizations
     * @param $client_id
     * @return \App\BaseModel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function listByClient($client_id)
    {
        $query = ClientAuthorization::where('client_id', $client_id)->ordered();
        $auths = $query->get();

        foreach($auths as $auth) {
            $auth->load('service');
            $auth['effective_start'] = Carbon::parse($auth['effective_start'])->format('m/d/Y');
            $auth['effective_end'] = Carbon::parse($auth['effective_end'])->format('m/d/Y');
            $auth['effective_start_sortable'] = Carbon::parse($auth['effective_start'])->format('Y-m-d');
            $auth['effective_end_sortable'] = Carbon::parse($auth['effective_end'])->format('Y-m-d');
            $auth['service_code'] = optional($auth['service'])->code;
            $auth['service_type'] = optional($auth['service'])->name;
        }

        return $auths;
    }

    /**
     * Store a newly created authorization in storage.
     *
     * @param \App\Http\Requests\CreateClientAuthRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClientAuthRequest $request)
    {
        $client = Client::forRequestedBusinesses()
            ->where('id', $request->client_id)
            ->first();

        $this->authorize('update', $client);

        $data = $request->filtered();

        if ($auth = ClientAuthorization::create($request->filtered())) {
            $auth['effective_start'] = Carbon::parse($auth['effective_start'])->format('m/d/Y');
            $auth['effective_end'] = Carbon::parse($auth['effective_end'])->format('m/d/Y');
            $auth['effective_start_sortable'] = Carbon::parse($auth['effective_start'])->format('Y-m-d');
            $auth['effective_end_sortable'] = Carbon::parse($auth['effective_end'])->format('Y-m-d');
            $auth['service_code'] = optional($auth['service'])->code;
            $auth['service_type'] = optional($auth['service'])->name;
            return new CreatedResponse('New authorization has been created', $auth->load('service'));
        }
        
        return new ErrorResponse(500, 'The authorization could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\CreateClientAuthRequest $request
     * @param \App\Billing\ClientAuthorization $auth
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(CreateClientAuthRequest $request, ClientAuthorization $auth)
    {
        $auth->load('client');
        $this->authorize('update', $auth->client);

        if ($auth->update($request->filtered())) {
            $auth['effective_start'] = Carbon::parse($auth['effective_start'])->format('m/d/Y');
            $auth['effective_end'] = Carbon::parse($auth['effective_end'])->format('m/d/Y');
            $auth['effective_start_sortable'] = Carbon::parse($auth['effective_start'])->format('Y-m-d');
            $auth['effective_end_sortable'] = Carbon::parse($auth['effective_end'])->format('Y-m-d');
            $auth['service_code'] = optional($auth['service'])->code;
            $auth['service_type'] = optional($auth['service'])->name;
            return new SuccessResponse('Authorization has been updated.', $auth->load('service'));
        }

        return new ErrorResponse(500, 'The authorization could not be updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Billing\ClientAuthorization $auth
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClientAuthorization $auth)
    {
        $auth->load('client');
        $this->authorize('update', $auth->client);

        try {
            if ($auth->delete()) {
                return new SuccessResponse('Authorization has been deleted.');
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }

        return new ErrorResponse(500, 'The authorization could not be deleted.');
    }
}
